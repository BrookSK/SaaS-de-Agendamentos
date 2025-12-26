<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\AsaasSetting;
use App\Models\AsaasWebhookEvent;
use App\Models\TenantSubscriptionAsaas;

final class WebhooksAsaasController
{
    /** @param array<string,string> $params */
    public function handle(Request $request, array $params): Response
    {
        // Token simples via header (configure em /super/asaas)
        $expected = AsaasSetting::get()['webhook_token'] ?? null;
        $provided = $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? null;

        if ($expected !== null && $expected !== '' && $provided !== $expected) {
            return Response::html('Unauthorized', 401);
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || $raw === '') {
            return Response::html('Bad Request', 400);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return Response::html('Bad JSON', 400);
        }

        $eventType = isset($decoded['event']) ? (string)$decoded['event'] : null;
        $paymentId = null;
        $subscriptionId = null;

        if (isset($decoded['payment']['id'])) {
            $paymentId = (string)$decoded['payment']['id'];
        }
        if (isset($decoded['subscription']['id'])) {
            $subscriptionId = (string)$decoded['subscription']['id'];
        }

        $resourceId = $subscriptionId ?? $paymentId;

        $eventId = AsaasWebhookEvent::create($eventType, $resourceId, $raw);

        try {
            if ($subscriptionId !== null) {
                $status = null;
                if (isset($decoded['subscription']['status'])) {
                    $status = (string)$decoded['subscription']['status'];
                }
                TenantSubscriptionAsaas::updateFromWebhook($subscriptionId, $status);
            }

            AsaasWebhookEvent::markProcessed($eventId);
        } catch (\Throwable $e) {
            AsaasWebhookEvent::markFailed($eventId, $e->getMessage());
            return Response::html('Error', 500);
        }

        return Response::html('OK', 200);
    }
}
