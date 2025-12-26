<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\AsaasSetting;
use App\Models\AsaasWebhookEvent;

final class SuperAdminAsaasController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/asaas/index', [
            'settings' => AsaasSetting::get(),
            'events' => AsaasWebhookEvent::latest(30),
            'message' => $request->query('message'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $env = (string)$request->input('environment', 'sandbox');
        if (!in_array($env, ['sandbox', 'production'], true)) {
            $env = 'sandbox';
        }

        $sandboxKey = trim((string)$request->input('api_key_sandbox', ''));
        $prodKey = trim((string)$request->input('api_key_production', ''));
        $webhookToken = trim((string)$request->input('webhook_token', ''));

        AsaasSetting::update(
            $env,
            $sandboxKey !== '' ? $sandboxKey : null,
            $prodKey !== '' ? $prodKey : null,
            $webhookToken !== '' ? $webhookToken : null
        );

        return Response::redirect('/super/asaas?message=Salvo');
    }
}
