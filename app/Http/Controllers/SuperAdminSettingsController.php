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
        'branding.logo_path',
        'branding.favicon_path',
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

        $projectRoot = dirname(__DIR__, 3);
        $uploadDirPublic = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'branding';
        $uploadDirRoot = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'branding';

        if (!is_dir($uploadDirPublic)) {
            @mkdir($uploadDirPublic, 0775, true);
        }
        if (!is_dir($uploadDirRoot)) {
            @mkdir($uploadDirRoot, 0775, true);
        }

        if (!is_dir($uploadDirPublic) || !is_dir($uploadDirRoot)) {
            return Response::redirect('/super/settings?error=' . rawurlencode('Falha ao criar pasta de uploads (branding).'));
        }

        $saveUploaded = static function (string $field, array $allowedExt) use ($uploadDirPublic, $uploadDirRoot): ?string {
            if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
                return null;
            }

            $f = $_FILES[$field];
            if (!isset($f['error'], $f['tmp_name'], $f['name']) || (int)$f['error'] !== UPLOAD_ERR_OK) {
                return null;
            }

            $orig = (string)$f['name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if ($ext === '' || !in_array($ext, $allowedExt, true)) {
                return null;
            }

            $tmp = (string)$f['tmp_name'];
            if (!is_uploaded_file($tmp)) {
                return null;
            }

            $base = $field . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
            $filename = $base . '.' . $ext;
            $destPublic = $uploadDirPublic . DIRECTORY_SEPARATOR . $filename;
            $destRoot = $uploadDirRoot . DIRECTORY_SEPARATOR . $filename;

            $moved = false;
            if (@move_uploaded_file($tmp, $destPublic)) {
                $moved = true;
                @copy($destPublic, $destRoot);
            } elseif (@move_uploaded_file($tmp, $destRoot)) {
                $moved = true;
                @copy($destRoot, $destPublic);
            }

            if (!$moved) {
                return null;
            }

            return '/uploads/branding/' . $filename;
        };

        $uploadErrors = [];

        $newLogo = $saveUploaded('branding_logo', ['png', 'jpg', 'jpeg', 'webp', 'svg']);
        if (is_string($newLogo) && $newLogo !== '') {
            $values['branding.logo_path'] = $newLogo;
        } elseif (isset($_FILES['branding_logo']) && is_array($_FILES['branding_logo'])) {
            $err = (int)($_FILES['branding_logo']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($err !== UPLOAD_ERR_NO_FILE) {
                $uploadErrors[] = $err === UPLOAD_ERR_OK
                    ? 'Logo (não foi possível salvar: verifique permissões da pasta uploads/branding)'
                    : 'Logo (erro de upload PHP #' . $err . ')';
            }
        }

        $newFavicon = $saveUploaded('branding_favicon', ['ico', 'png', 'jpg', 'jpeg', 'webp', 'svg']);
        if (is_string($newFavicon) && $newFavicon !== '') {
            $values['branding.favicon_path'] = $newFavicon;
        } elseif (isset($_FILES['branding_favicon']) && is_array($_FILES['branding_favicon'])) {
            $err = (int)($_FILES['branding_favicon']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($err !== UPLOAD_ERR_NO_FILE) {
                $uploadErrors[] = $err === UPLOAD_ERR_OK
                    ? 'Favicon (não foi possível salvar: verifique permissões da pasta uploads/branding)'
                    : 'Favicon (erro de upload PHP #' . $err . ')';
            }
        }

        if (!in_array($values['smtp.encryption'], ['none', 'tls', 'ssl'], true)) {
            $values['smtp.encryption'] = 'none';
        }

        SystemSetting::setMany($values);

        if ($uploadErrors !== []) {
            return Response::redirect('/super/settings?error=' . rawurlencode('Falha ao salvar: ' . implode(', ', $uploadErrors) . '. Verifique permissões/formatos.'));
        }

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
