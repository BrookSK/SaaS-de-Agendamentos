<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\SmtpMailer;
use App\Core\Url;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;

final class SuperAdminTenantsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/tenants/index', [
            'tenants' => Tenant::all(),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        $slug = trim((string)$request->input('slug', ''));
        $status = (string)$request->input('status', 'active');
        $email = trim((string)$request->input('email', ''));
        $phone = trim((string)$request->input('phone', ''));
        $cpfCnpj = trim((string)$request->input('cpf_cnpj', ''));

        $adminEmail = trim((string)$request->input('admin_email', ''));
        $adminPassword = (string)$request->input('admin_password', '');

        if ($slug === '' && $name !== '') {
            $slug = mb_strtolower($name);
            $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug) ?? $slug;
            $slug = trim($slug, '-');
            $slug = preg_replace('/-+/', '-', $slug) ?? $slug;
        }

        if ($name === '' || $slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            return Response::redirect('/super/tenants');
        }

        if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return Response::redirect('/super/tenants');
        }

        if (mb_strlen($adminPassword) < 6) {
            return Response::redirect('/super/tenants');
        }

        if (!in_array($status, ['active', 'blocked'], true)) {
            $status = 'active';
        }

        $pdo = Db::pdo();
        $emailStatus = 'skipped';

        try {
            $pdo->beginTransaction();

            $tenantId = Tenant::createReturningId(
                $name,
                $slug,
                $status,
                $email !== '' ? $email : null,
                $phone !== '' ? $phone : null,
                $cpfCnpj !== '' ? $cpfCnpj : null
            );

            if (User::findByEmail($adminEmail, $tenantId) !== null) {
                $pdo->rollBack();
                return Response::redirect('/super/tenants');
            }

            $adminName = 'Admin ' . $name;
            $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
            User::createTenantAdmin($tenantId, $adminName, $adminEmail, $hash, 'active');

            $pdo->commit();

            $appCfg = require dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
            $fallbackUrl = (string)($appCfg['url'] ?? 'http://localhost');
            $baseUrl = Url::base($fallbackUrl);
            $loginUrl = rtrim($baseUrl, '/') . '/t/' . rawurlencode($slug) . '/login';

            $systemName = (string)SystemSetting::get('system.name', 'Agenda SaaS');
            $smtpHost = (string)SystemSetting::get('smtp.host', '');
            $smtpPort = (int)(SystemSetting::get('smtp.port', '0') ?? '0');
            $smtpEnc = (string)SystemSetting::get('smtp.encryption', 'none');
            $smtpUser = (string)SystemSetting::get('smtp.username', '');
            $smtpPass = (string)SystemSetting::get('smtp.password', '');
            $fromEmail = (string)SystemSetting::get('smtp.from_email', '');
            $fromName = (string)SystemSetting::get('smtp.from_name', $systemName);

            if ($smtpHost !== '' && $smtpPort > 0 && $fromEmail !== '') {
                try {
                    $mailer = new SmtpMailer(
                        $smtpHost,
                        $smtpPort,
                        $smtpEnc,
                        $smtpUser !== '' ? $smtpUser : null,
                        $smtpPass !== '' ? $smtpPass : null,
                        $fromEmail,
                        $fromName !== '' ? $fromName : $systemName
                    );

                    $subject = 'Bem-vindo ao ' . $systemName . ' — acesso da empresa';
                    $htmlBody = '<div style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;max-width:640px;margin:0 auto;padding:22px;background:#f5f7fb;color:#0f172a">'
                        . '<div style="background:#ffffff;border:1px solid rgba(15,23,42,.10);border-radius:14px;box-shadow:0 12px 30px rgba(15,23,42,.10);padding:18px">'
                        . '<h1 style="margin:0 0 10px;font-size:22px;letter-spacing:-.02em">Bem-vindo(a)!</h1>'
                        . '<p style="margin:0 0 12px;color:#64748b">Sua empresa <strong>' . htmlspecialchars($name) . '</strong> foi criada no <strong>' . htmlspecialchars($systemName) . '</strong>.</p>'
                        . '<div style="margin:14px 0;padding:12px;border-radius:12px;border:1px solid rgba(15,23,42,.10);background:#ffffff">'
                        . '<div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Link de acesso</div>'
                        . '<a href="' . htmlspecialchars($loginUrl) . '" style="color:#2563eb;font-weight:700;text-decoration:none">' . htmlspecialchars($loginUrl) . '</a>'
                        . '</div>'
                        . '<div style="margin:14px 0;padding:12px;border-radius:12px;border:1px solid rgba(15,23,42,.10);background:#ffffff">'
                        . '<div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">Credenciais</div>'
                        . '<div style="color:#0f172a"><strong>E-mail:</strong> ' . htmlspecialchars($adminEmail) . '</div>'
                        . '<div style="color:#0f172a"><strong>Senha:</strong> ' . htmlspecialchars($adminPassword) . '</div>'
                        . '</div>'
                        . '<p style="margin:14px 0 0;color:#64748b">Se você não solicitou esse cadastro, ignore este e-mail.</p>'
                        . '</div>'
                        . '<div style="margin-top:12px;color:#64748b;font-size:12px">© ' . date('Y') . ' ' . htmlspecialchars($systemName) . '</div>'
                        . '</div>';

                    $mailer->send($adminEmail, $subject, $htmlBody);
                    $emailStatus = 'sent';
                } catch (\Throwable $e) {
                    $emailStatus = 'failed';
                }
            } else {
                $emailStatus = 'not_configured';
            }

            return Response::redirect('/super/tenants?created=1&slug=' . rawurlencode($slug) . '&login_url=' . rawurlencode($loginUrl) . '&admin_email=' . rawurlencode($adminEmail) . '&email_status=' . rawurlencode($emailStatus));
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Response::redirect('/super/tenants');
        }
    }

    /** @param array<string,string> $params */
    public function edit(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return Response::redirect('/super/tenants');
        }

        $tenant = Tenant::findById($id);
        if ($tenant === null) {
            return Response::redirect('/super/tenants');
        }

        return $this->view('super/tenants/edit', [
            'tenant' => $tenant,
        ]);
    }

    /** @param array<string,string> $params */
    public function update(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            return Response::redirect('/super/tenants');
        }

        $name = trim((string)$request->input('name', ''));
        $slug = trim((string)$request->input('slug', ''));
        $status = (string)$request->input('status', 'active');
        $email = trim((string)$request->input('email', ''));
        $phone = trim((string)$request->input('phone', ''));
        $cpfCnpj = trim((string)$request->input('cpf_cnpj', ''));

        if ($name === '' || $slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            return Response::redirect('/super/tenants/' . $id . '/edit');
        }

        if (!in_array($status, ['active', 'blocked'], true)) {
            $status = 'active';
        }

        Tenant::updateById(
            $id,
            $name,
            $slug,
            $status,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
            $cpfCnpj !== '' ? $cpfCnpj : null
        );

        return Response::redirect('/super/tenants');
    }
}
