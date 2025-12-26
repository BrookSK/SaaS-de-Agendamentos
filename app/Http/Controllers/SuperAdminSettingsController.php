<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\SmtpMailer;
use App\Models\SystemSetting;

final class SuperAdminSettingsController extends Controller
{
    private const KEYS = [
        'system.name',
        'system.description',
        'smtp.host',
        'smtp.port',
        'smtp.encryption',
        'smtp.username',
        'smtp.password',
        'smtp.from_email',
        'smtp.from_name',
    ];

    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $settings = SystemSetting::getMany(self::KEYS);

        return $this->view('super/settings/index', [
            'settings' => $settings,
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

        $values = [
            'system.name' => trim((string)$request->input('system_name', '')),
            'system.description' => trim((string)$request->input('system_description', '')),
            'smtp.host' => trim((string)$request->input('smtp_host', '')),
            'smtp.port' => trim((string)$request->input('smtp_port', '')),
            'smtp.encryption' => trim((string)$request->input('smtp_encryption', 'none')),
            'smtp.username' => trim((string)$request->input('smtp_username', '')),
            'smtp.password' => (string)$request->input('smtp_password', ''),
            'smtp.from_email' => trim((string)$request->input('smtp_from_email', '')),
            'smtp.from_name' => trim((string)$request->input('smtp_from_name', '')),
        ];

        if (!in_array($values['smtp.encryption'], ['none', 'tls', 'ssl'], true)) {
            $values['smtp.encryption'] = 'none';
        }

        SystemSetting::setMany($values);

        return Response::redirect('/super/settings?message=Salvo');
    }

    /** @param array<string,string> $params */
    public function testEmail(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $to = trim((string)$request->input('to', ''));
        if ($to === '') {
            return Response::redirect('/super/settings?error=Informe%20um%20destinat%C3%A1rio');
        }

        $host = (string)SystemSetting::get('smtp.host', '');
        $port = (int)(SystemSetting::get('smtp.port', '0') ?? '0');
        $enc = (string)SystemSetting::get('smtp.encryption', 'none');
        $user = (string)SystemSetting::get('smtp.username', '');
        $pass = (string)SystemSetting::get('smtp.password', '');
        $fromEmail = (string)SystemSetting::get('smtp.from_email', '');
        $fromName = (string)SystemSetting::get('smtp.from_name', 'Sistema');

        if ($host === '' || $port <= 0 || $fromEmail === '') {
            return Response::redirect('/super/settings?error=Configure%20SMTP%20(host%2Fporta%2Ffrom)');
        }

        try {
            $mailer = new SmtpMailer(
                $host,
                $port,
                $enc,
                $user !== '' ? $user : null,
                $pass !== '' ? $pass : null,
                $fromEmail,
                $fromName !== '' ? $fromName : 'Sistema'
            );

            $mailer->send($to, 'Teste SMTP', '<p>SMTP OK.</p>');
        } catch (\Throwable $e) {
            return Response::redirect('/super/settings?error=' . rawurlencode('Falha: ' . $e->getMessage()));
        }

        return Response::redirect('/super/settings?message=E-mail%20enviado');
    }
}
