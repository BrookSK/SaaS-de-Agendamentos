<?php

declare(strict_types=1);

namespace App\Core;

final class AsaasClient
{
    public function __construct(
        private string $apiKey,
        private string $environment // sandbox|production
    ) {}

    /** @return array<string,mixed> */
    public function createCustomer(string $name, string $email, ?string $phone, string $cpfCnpj): array
    {
        return $this->request('POST', '/customers', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'cpfCnpj' => $cpfCnpj,
        ]);
    }

    /** @return array<string,mixed> */
    public function createSubscription(
        string $customerId,
        string $billingType,
        float $value,
        string $cycle,
        string $description,
        ?string $externalReference = null
    ): array {
        $payload = [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => $value,
            'cycle' => $cycle,
            'description' => $description,
        ];

        if ($externalReference !== null) {
            $payload['externalReference'] = $externalReference;
        }

        return $this->request('POST', '/subscriptions', $payload);
    }

    /** @return array<string,mixed> */
    private function request(string $method, string $path, ?array $jsonBody = null): array
    {
        $base = $this->environment === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';

        $url = $base . $path;

        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Falha ao iniciar cURL');
        }

        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey,
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($jsonBody !== null) {
            $payload = json_encode($jsonBody);
            if (!is_string($payload)) {
                throw new \RuntimeException('Falha ao serializar JSON');
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Erro cURL: ' . $err);
        }

        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($resp, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Resposta inválida do Asaas (HTTP ' . $code . '): ' . $resp);
        }

        if ($code >= 400) {
            $msg = $decoded['errors'][0]['description'] ?? ($decoded['message'] ?? 'Erro Asaas');
            throw new \RuntimeException('Asaas HTTP ' . $code . ': ' . (string)$msg);
        }

        return $decoded;
    }
}
