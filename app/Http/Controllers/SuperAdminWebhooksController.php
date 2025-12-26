<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\WebhookDispatcher;
use App\Models\SystemWebhook;
use App\Models\SystemWebhookDelivery;

final class SuperAdminWebhooksController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/webhooks/index', [
            'webhooks' => SystemWebhook::all(),
            'deliveries' => SystemWebhookDelivery::latest(200),
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $event = trim((string)$request->input('event_name', ''));
        $url = trim((string)$request->input('url', ''));
        $secret = trim((string)$request->input('secret', ''));
        $environment = (string)$request->input('environment', 'test');
        $active = (int)$request->input('active', 1);

        if ($event === '' || $url === '' || !in_array($environment, ['test', 'production'], true)) {
            return Response::redirect('/super/webhooks?error=' . rawurlencode('Dados inválidos'));
        }

        SystemWebhook::create(
            $event,
            $url,
            $secret !== '' ? $secret : null,
            $environment,
            $active === 1 ? 1 : 0
        );

        return Response::redirect('/super/webhooks?message=Salvo');
    }

    /** @param array<string,string> $params */
    public function resend(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $id = (int)$request->input('delivery_id', 0);
        if ($id <= 0) {
            return Response::redirect('/super/webhooks?error=' . rawurlencode('ID inválido'));
        }

        WebhookDispatcher::resend($id);
        return Response::redirect('/super/webhooks?message=Reenviado');
    }

    /** @param array<string,string> $params */
    public function processQueue(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $processed = WebhookDispatcher::processDue(50);
        return Response::redirect('/super/webhooks?message=' . rawurlencode('Processados: ' . $processed));
    }
}
