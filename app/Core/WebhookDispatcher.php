<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Db;

final class WebhookDispatcher
{
    /**
     * @param array<string,mixed> $payload
     */
    public static function dispatch(string $eventName, array $payload): void
    {
        $env = getenv('APP_ENV') ?: 'local';
        $environment = ($env === 'production') ? 'production' : 'test';

        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM system_webhooks WHERE active = 1 AND environment = :env AND event_name = :event');
        $stmt->execute(['env' => $environment, 'event' => $eventName]);
        $webhooks = $stmt->fetchAll();

        if (!is_array($webhooks) || $webhooks === []) {
            return;
        }

        $payload['event'] = $eventName;
        $payload['timestamp'] = date('c');

        $json = json_encode($payload);
        if (!is_string($json)) {
            return;
        }

        foreach ($webhooks as $wh) {
            $webhookId = (int)$wh['id'];
            $url = (string)$wh['url'];
            $secret = $wh['secret'] !== null ? (string)$wh['secret'] : '';

            $deliveryId = self::createDelivery($webhookId, $eventName, $json);
            self::attemptDelivery($deliveryId, $url, $secret, $eventName, $json);
        }
    }

    public static function resend(int $deliveryId): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.*, w.url, w.secret
             FROM system_webhook_deliveries d
             INNER JOIN system_webhooks w ON w.id = d.webhook_id
             WHERE d.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $deliveryId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return;
        }

        $json = (string)$row['payload_json'];
        $url = (string)$row['url'];
        $secret = $row['secret'] !== null ? (string)$row['secret'] : '';
        $eventName = (string)$row['event_name'];

        self::attemptDelivery($deliveryId, $url, $secret, $eventName, $json);
    }

    public static function processDue(int $limit = 50): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.id
             FROM system_webhook_deliveries d
             WHERE d.status = "failed" AND d.next_attempt_at IS NOT NULL AND d.next_attempt_at <= NOW()
             ORDER BY d.next_attempt_at ASC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows) || $rows === []) {
            return 0;
        }

        $count = 0;
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $id = (int)($r['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            self::resend($id);
            $count++;
        }

        return $count;
    }

    private static function createDelivery(int $webhookId, string $eventName, string $payloadJson): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO system_webhook_deliveries (webhook_id, event_name, payload_json, status)
             VALUES (:webhook_id, :event_name, CAST(:payload_json AS JSON), "pending")'
        );
        $stmt->execute([
            'webhook_id' => $webhookId,
            'event_name' => $eventName,
            'payload_json' => $payloadJson,
        ]);
        return (int)$pdo->lastInsertId();
    }

    private static function attemptDelivery(int $deliveryId, string $url, string $secret, string $eventName, string $payloadJson): void
    {
        $pdo = Db::pdo();

        $signature = $secret !== '' ? hash_hmac('sha256', $payloadJson, $secret) : '';
        $timestamp = (string)time();

        $code = null;
        $respBody = null;
        $error = null;

        try {
            if (!function_exists('curl_init')) {
                throw new \RuntimeException('cURL não habilitado');
            }

            $ch = curl_init($url);
            if ($ch === false) {
                throw new \RuntimeException('Falha ao iniciar cURL');
            }

            $headers = [
                'Content-Type: application/json',
                'X-Event: ' . $eventName,
                'X-Timestamp: ' . $timestamp,
            ];
            if ($signature !== '') {
                $headers[] = 'X-Signature: ' . $signature;
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $resp = curl_exec($ch);
            if ($resp === false) {
                $error = curl_error($ch);
            } else {
                $respBody = $resp;
            }

            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($error !== null || $code < 200 || $code >= 300) {
                throw new \RuntimeException($error !== null ? $error : ('HTTP ' . $code));
            }

            $stmt = $pdo->prepare(
                'UPDATE system_webhook_deliveries
                 SET status = "success", attempt_count = attempt_count + 1, last_attempt_at = NOW(), response_code = :code, response_body = :body, error = NULL, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'id' => $deliveryId,
                'code' => $code,
                'body' => $respBody,
            ]);
            return;
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $stmt = $pdo->prepare(
            'UPDATE system_webhook_deliveries
             SET status = "failed", attempt_count = attempt_count + 1, last_attempt_at = NOW(), next_attempt_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), response_code = :code, response_body = :body, error = :err, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $deliveryId,
            'code' => $code,
            'body' => $respBody,
            'err' => $error,
        ]);
    }
}
