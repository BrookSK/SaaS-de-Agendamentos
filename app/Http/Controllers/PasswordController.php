<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\SmtpMailer;
use App\Core\Tenant;
use App\Models\PasswordReset;
use App\Models\SystemSetting;
use App\Models\User;

final class PasswordController extends Controller
{
    /** @param array<string,string> $params */
    public function showForgot(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        return $this->view('auth/forgot_password', [
            'tenant' => $tenant,
            'message' => $request->query('message'),
        ]);
    }

    /** @param array<string,string> $params */
    public function sendReset(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        $tenantId = $tenant?->tenantId;

        $email = trim((string)$request->input('email', ''));
        if ($email === '') {
            $prefix = $tenant?->urlPrefix() ?? '';
            return Response::redirect($prefix . '/forgot-password?message=' . rawurlencode('Informe um e-mail'));
        }

        $user = User::findByEmail($email, $tenantId);
        // Sempre responder de forma genérica
        if ($user === null) {
            $prefix = $tenant?->urlPrefix() ?? '';
            return Response::redirect($prefix . '/forgot-password?message=' . rawurlencode('Se o e-mail existir, enviaremos um link de recuperação.'));
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        PasswordReset::create($user->id, $user->tenantId, $tokenHash, $expiresAt);

        $prefix = $tenant?->urlPrefix() ?? '';
        $appUrl = getenv('APP_URL') ?: 'http://localhost';
        $resetUrl = rtrim($appUrl, '/') . $prefix . '/reset-password?token=' . rawurlencode($token);

        $smtpHost = (string)SystemSetting::get('smtp.host', '');
        $smtpPort = (int)(SystemSetting::get('smtp.port', '0') ?? '0');
        $smtpEnc = (string)SystemSetting::get('smtp.encryption', 'none');
        $smtpUser = (string)SystemSetting::get('smtp.username', '');
        $smtpPass = (string)SystemSetting::get('smtp.password', '');
        $fromEmail = (string)SystemSetting::get('smtp.from_email', '');
        $fromName = (string)SystemSetting::get('smtp.from_name', 'Sistema');

        if ($smtpHost !== '' && $smtpPort > 0 && $fromEmail !== '') {
            try {
                $mailer = new SmtpMailer(
                    $smtpHost,
                    $smtpPort,
                    $smtpEnc,
                    $smtpUser !== '' ? $smtpUser : null,
                    $smtpPass !== '' ? $smtpPass : null,
                    $fromEmail,
                    $fromName !== '' ? $fromName : 'Sistema'
                );

                $mailer->send(
                    $email,
                    'Recuperação de senha',
                    '<p>Clique para redefinir sua senha:</p><p><a href="' . htmlspecialchars($resetUrl) . '">' . htmlspecialchars($resetUrl) . '</a></p><p>Este link expira em 1 hora.</p>'
                );

                return Response::redirect($prefix . '/forgot-password?message=' . rawurlencode('Se o e-mail existir, enviaremos um link de recuperação.'));
            } catch (\Throwable $e) {
                // fallback: exibir link (modo dev)
                return $this->view('auth/forgot_password', [
                    'tenant' => $tenant,
                    'message' => 'SMTP falhou. Link para teste: ' . $resetUrl,
                ]);
            }
        }

        // fallback: exibir link (modo dev)
        return $this->view('auth/forgot_password', [
            'tenant' => $tenant,
            'message' => 'SMTP não configurado. Link para teste: ' . $resetUrl,
        ]);
    }

    /** @param array<string,string> $params */
    public function showReset(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        $token = (string)$request->query('token', '');
        if ($token === '') {
            return Response::html('Token inválido', 400);
        }

        return $this->view('auth/reset_password', [
            'tenant' => $tenant,
            'token' => $token,
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function reset(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        $prefix = $tenant?->urlPrefix() ?? '';

        $token = (string)$request->input('token', '');
        $password = (string)$request->input('password', '');
        $password2 = (string)$request->input('password_confirm', '');

        if ($token === '' || $password === '' || $password !== $password2) {
            return Response::redirect($prefix . '/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Dados inválidos'));
        }

        $tokenHash = hash('sha256', $token);
        $row = PasswordReset::findValidByTokenHash($tokenHash);
        if ($row === null) {
            return Response::redirect($prefix . '/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Token inválido ou expirado'));
        }

        $userId = (int)$row['user_id'];
        $user = User::findById($userId);
        if ($user === null) {
            return Response::redirect($prefix . '/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Usuário não encontrado'));
        }

        // Garante tenant correto quando aplicável
        if ($tenant !== null && $tenant->tenantId !== null) {
            if ($user->tenantId !== $tenant->tenantId) {
                return Response::redirect($prefix . '/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Token não pertence a este tenant'));
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($hash)) {
            return Response::redirect($prefix . '/reset-password?token=' . rawurlencode($token) . '&error=' . rawurlencode('Falha ao gerar hash'));
        }

        User::updatePasswordHash($user->id, $hash);
        PasswordReset::markUsed((int)$row['id']);

        return Response::redirect($prefix . '/login?error=' . rawurlencode('Senha alterada. Faça login.'));
    }

    /** @param array<string,string> $params */
    public function showChange(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($resp = Auth::requireLogin()) {
            return $resp;
        }

        return $this->view('auth/change_password', [
            'tenant' => $tenant,
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function change(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        $prefix = $tenant?->urlPrefix() ?? '';

        if ($resp = Auth::requireLogin()) {
            return $resp;
        }

        $current = (string)$request->input('current_password', '');
        $password = (string)$request->input('password', '');
        $password2 = (string)$request->input('password_confirm', '');

        if ($password === '' || $password !== $password2) {
            return Response::redirect($prefix . '/change-password?error=' . rawurlencode('Senha nova inválida'));
        }

        $authUser = Auth::user();
        $userId = (int)($authUser['id'] ?? 0);
        $user = $userId > 0 ? User::findById($userId) : null;
        if ($user === null) {
            return Response::redirect($prefix . '/change-password?error=' . rawurlencode('Usuário inválido'));
        }

        if (!password_verify($current, $user->passwordHash)) {
            return Response::redirect($prefix . '/change-password?error=' . rawurlencode('Senha atual incorreta'));
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($hash)) {
            return Response::redirect($prefix . '/change-password?error=' . rawurlencode('Falha ao gerar hash'));
        }

        User::updatePasswordHash($user->id, $hash);

        return Response::redirect($prefix . '/change-password?message=' . rawurlencode('Senha alterada'));
    }
}
