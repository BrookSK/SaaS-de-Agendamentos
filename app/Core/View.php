<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\SystemSetting;

final class View
{
    public static function render(string $view, array $data = []): string
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        if (!is_file($path)) {
            return 'View não encontrada: ' . htmlspecialchars($view);
        }

        extract($data);
        ob_start();
        require $path;
        $html = (string)ob_get_clean();

        if (stripos($html, '<head') !== false && stripos($html, '/assets/app.css') === false) {
            $projectRoot = dirname(__DIR__, 2);
            $publicCss = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.css';
            $rootCss = $projectRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'app.css';

            $v = null;
            if (is_file($publicCss)) {
                $v = @filemtime($publicCss) ?: null;
            } elseif (is_file($rootCss)) {
                $v = @filemtime($rootCss) ?: null;
            }

            $href = '/assets/app.css' . ($v ? ('?v=' . $v) : '');
            $link = "\n    <link rel=\"stylesheet\" href=\"" . htmlspecialchars($href) . "\">\n";
            $html = preg_replace('/<head(\b[^>]*)>/i', '<head$1>' . $link, $html, 1) ?? $html;
        }

        $systemName = (string)SystemSetting::get('system.name', 'Agenda SaaS');
        $logoPath = (string)SystemSetting::get('branding.logo_path', '');
        $faviconPath = (string)SystemSetting::get('branding.favicon_path', '');

        if (stripos($html, '<head') !== false && $faviconPath !== '' && stripos($html, 'rel="icon"') === false) {
            $v = time();
            $iconHref = $faviconPath . '?v=' . $v;
            $iconLink = "\n    <link rel=\"icon\" href=\"" . htmlspecialchars($iconHref) . "\">\n";
            $html = preg_replace('/<head(\b[^>]*)>/i', '<head$1>' . $iconLink, $html, 1) ?? $html;
        }

        if ((str_starts_with($view, 'auth/') || str_starts_with($view, 'public/') || str_starts_with($view, 'home/')) && stripos($html, '<body') !== false) {
            $brand = $logoPath !== ''
                ? ('<img src="' . htmlspecialchars($logoPath) . '" alt="' . htmlspecialchars($systemName) . '" class="brand-logo">')
                : htmlspecialchars($systemName);
            $shellStart = '<body class="guest">'
                . '<header class="guest-header">'
                . '<div class="guest-brand">' . $brand . '</div>'
                . '</header>'
                . '<main class="guest-main">'
                . '<div class="guest-card">';

            $shellEnd = '</div>'
                . '</main>'
                . '<footer class="guest-footer">© ' . date('Y') . ' ' . htmlspecialchars($systemName) . '</footer>'
                . '</body>';

            $html = preg_replace('/<body(\b[^>]*)>/i', $shellStart, $html, 1) ?? $html;
            $html = preg_replace('/<\/body>/i', $shellEnd, $html, 1) ?? $html;
        }

        if ((str_starts_with($view, 'super/') || str_starts_with($view, 'tenant/')) && stripos($html, '<body') !== false) {
            $u = Auth::user();
            $role = (string)($u['role'] ?? '');
            $userEmail = (string)($u['email'] ?? '');
            $prefix = Tenant::current()?->urlPrefix() ?? '';

            $uriPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
            $icon = static function (string $name): string {
                return match ($name) {
                    'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>',
                    'company' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 21V3h14v18H3zm2-2h10V5H5v14zm14 2v-9h2v9h-2z"/></svg>',
                    'plans' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6 2h12a2 2 0 0 1 2 2v16l-4-3-4 3-4-3-4 3V4a2 2 0 0 1 2-2z"/></svg>',
                    'subscriptions' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 3h10v2H7V3zm0 4h10v2H7V7zm-2 6h14v8H5v-8zm2 2v4h10v-4H7z"/></svg>',
                    'webhooks' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 4a4 4 0 0 1 4 4h-2a2 2 0 1 0-2 2h1a5 5 0 0 1 0 10H9a4 4 0 0 1-4-4h2a2 2 0 1 0 2-2H8a5 5 0 0 1 0-10h4z"/></svg>',
                    'audit' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l8 4v6c0 5-3.5 9.7-8 10-4.5-.3-8-5-8-10V6l8-4zm0 2.2L6 6.5v5.3c0 3.8 2.5 7.4 6 8 3.5-.6 6-4.2 6-8V6.5l-6-2.3z"/></svg>',
                    'settings' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19.14 12.94a7.43 7.43 0 0 0 .05-.94 7.43 7.43 0 0 0-.05-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.2 7.2 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.9 1h-3.8a.5.5 0 0 0-.49.42l-.36 2.54c-.58.23-1.12.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.71 7.48a.5.5 0 0 0 .12.64l2.03 1.58c-.03.31-.05.63-.05.94s.02.63.05.94L2.83 14.52a.5.5 0 0 0-.12.64l1.92 3.32c.13.22.39.3.6.22l2.39-.96c.51.4 1.05.71 1.63.94l.36 2.54c.04.24.25.42.49.42h3.8c.24 0 .45-.18.49-.42l.36-2.54c.58-.23 1.12-.54 1.63-.94l2.39.96c.22.09.47 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5z"/></svg>',
                    'payments' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7zm2 0v1h14V7H5zm0 4v6h14v-6H5z"/></svg>',
                    'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3V2zm15 8H2v10h20V10z"/></svg>',
                    'services' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.7 20.3l-6.1-6.1 1.4-1.4 6.1 6.1-1.4 1.4zM10 18a8 8 0 1 1 5.3-14l-1.4 1.4A6 6 0 1 0 16 10c0 1.6-.6 3-1.6 4.1L18 17.7A7.97 7.97 0 0 1 10 18z"/></svg>',
                    'employees' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16 11c1.7 0 3-1.3 3-3S17.7 5 16 5s-3 1.3-3 3 1.3 3 3 3zM8 11c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm0 2c-2.3 0-7 1.2-7 3.5V19h14v-2.5C15 14.2 10.3 13 8 13zm8 0c-.3 0-.6 0-.9.1 1.1.8 1.9 1.8 1.9 3.4V19h7v-2.5c0-2.3-4.7-3.5-7-3.5z"/></svg>',
                    'clients' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-3.3 0-8 1.7-8 5v3h16v-3c0-3.3-4.7-5-8-5z"/></svg>',
                    'finance' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1a11 11 0 1 0 11 11A11 11 0 0 0 12 1zm1 17.9V20h-2v-1.1a4.6 4.6 0 0 1-3.2-1.8l1.6-1.6A3 3 0 0 0 12 16a2 2 0 0 0 2-1.8c0-1.2-1-1.7-2.6-2.1-2-.5-3.9-1.2-3.9-3.6A3.6 3.6 0 0 1 11 5.1V4h2v1.1a4.3 4.3 0 0 1 2.7 1.5l-1.5 1.5A2.6 2.6 0 0 0 12 7a1.7 1.7 0 0 0-1.9 1.6c0 1 1 1.4 2.7 1.8 2 .5 3.8 1.3 3.8 3.8a3.8 3.8 0 0 1-3.6 3.7z"/></svg>',
                    'reports' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 3h18v2H3V3zm2 6h4v10H5V9zm6 4h4v6h-4v-6zm6-2h4v8h-4v-8z"/></svg>',
                    'notifications' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6V11a6 6 0 0 0-5-5.9V4a1 1 0 1 0-2 0v1.1A6 6 0 0 0 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>',
                    default => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/></svg>',
                };
            };

            $makeNavLink = static function (string $href, string $label, string $iconSvg) use ($uriPath): string {
                $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? $href);
                $isActive = $hrefPath === '/' ? $uriPath === '/' : ($uriPath === $hrefPath || str_starts_with($uriPath, rtrim($hrefPath, '/') . '/'));
                $cls = 'nav-item' . ($isActive ? ' active' : '');
                return '<a class="' . $cls . '" href="' . htmlspecialchars($href) . '">' . '<span class="nav-item-icon">' . $iconSvg . '</span>' . '<span class="nav-item-label">' . htmlspecialchars($label) . '</span>' . '</a>';
            };

            $navLinks = '';
            if ($role === 'super_admin') {
                $navLinks .= $makeNavLink('/super/dashboard', 'Dashboard', $icon('dashboard')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/tenants', 'Empresas', $icon('company')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/plans', 'Planos', $icon('plans')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/subscriptions', 'Assinaturas', $icon('subscriptions')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/webhooks', 'Webhooks', $icon('webhooks')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/audit', 'Auditoria', $icon('audit')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/settings', 'Configurações', $icon('settings')) . PHP_EOL;
                $navLinks .= $makeNavLink('/super/asaas', 'Asaas', $icon('payments')) . PHP_EOL;
            } else {
                $navLinks .= $makeNavLink($prefix . '/dashboard', 'Dashboard', $icon('dashboard')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/agenda', 'Agenda', $icon('calendar')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/services', 'Serviços', $icon('services')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/employees', 'Profissionais', $icon('employees')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/clients', 'Clientes', $icon('clients')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/finance', 'Financeiro', $icon('finance')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/reports', 'Relatórios', $icon('reports')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/settings/notifications', 'Notificações', $icon('notifications')) . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/audit', 'Auditoria', $icon('audit')) . PHP_EOL;
            }

            $logoutAction = $role === 'super_admin' ? '/logout' : ($prefix . '/logout');

            $brand = $logoPath !== ''
                ? ('<img src="' . htmlspecialchars($logoPath) . '" alt="' . htmlspecialchars($systemName) . '" class="brand-logo">')
                : htmlspecialchars($systemName);
            $shellStart = '<body class="app">'
                . '<header class="app-header">'
                . '<div class="app-brand">' . $brand . '</div>'
                . '<button class="app-menu-btn" type="button" aria-label="Menu" onclick="document.body.classList.toggle(\'nav-open\')">Menu</button>'
                . '<div class="app-user">'
                . '<span class="app-user-email">' . htmlspecialchars($userEmail) . '</span>'
                . '<form method="post" action="' . htmlspecialchars($logoutAction) . '"><button type="submit" class="btn">Sair</button></form>'
                . '</div>'
                . '</header>'
                . '<div class="app-shell">'
                . '<nav class="app-nav">'
                . '<div class="nav-title">Menu</div>'
                . $navLinks
                . '</nav>'
                . '<main class="app-main">';

            $shellEnd = '</main>'
                . '</div>'
                . '<footer class="app-footer">© ' . date('Y') . ' ' . htmlspecialchars($systemName) . '</footer>'
                . '<script>(function(){function closeNav(){document.body.classList.remove("nav-open");}document.addEventListener("click",function(e){var nav=document.querySelector(".app-nav");var btn=document.querySelector(".app-menu-btn");if(!nav||!btn){return;}if(document.body.classList.contains("nav-open")&&!nav.contains(e.target)&&!btn.contains(e.target)){closeNav();}});})();</script>'
                . '</body>';

            $html = preg_replace('/<body(\b[^>]*)>/i', $shellStart, $html, 1) ?? $html;
            $html = preg_replace('/<\/body>/i', $shellEnd, $html, 1) ?? $html;
        }

        $html = preg_replace('/<p>\s*<a\b[^>]*>\s*Voltar(?:\s+ao\s+login)?\s*<\/a>\s*<\/p>\s*/i', '', $html) ?? $html;
        $html = preg_replace('/<a\b[^>]*>\s*Voltar(?:\s+ao\s+login)?\s*<\/a>\s*/i', '', $html) ?? $html;
        $html = preg_replace('/<div\s+class="footer-links">\s*<\/div>/i', '', $html) ?? $html;

        return $html;
    }
}
