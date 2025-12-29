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
            $makeNavLink = static function (string $href, string $label) use ($uriPath): string {
                $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? $href);
                $isActive = $hrefPath === '/' ? $uriPath === '/' : ($uriPath === $hrefPath || str_starts_with($uriPath, rtrim($hrefPath, '/') . '/'));
                $cls = 'nav-item' . ($isActive ? ' active' : '');
                return '<a class="' . $cls . '" href="' . htmlspecialchars($href) . '">' . htmlspecialchars($label) . '</a>';
            };

            $navLinks = '';
            if ($role === 'super_admin') {
                $navLinks .= $makeNavLink('/super/dashboard', 'Dashboard') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/tenants', 'Empresas') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/plans', 'Planos') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/subscriptions', 'Assinaturas') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/webhooks', 'Webhooks') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/audit', 'Auditoria') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/settings', 'Configurações') . PHP_EOL;
                $navLinks .= $makeNavLink('/super/asaas', 'Asaas') . PHP_EOL;
            } else {
                $navLinks .= $makeNavLink($prefix . '/dashboard', 'Dashboard') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/agenda', 'Agenda') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/services', 'Serviços') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/employees', 'Profissionais') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/clients', 'Clientes') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/finance', 'Financeiro') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/reports', 'Relatórios') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/settings/notifications', 'Notificações') . PHP_EOL;
                $navLinks .= $makeNavLink($prefix . '/audit', 'Auditoria') . PHP_EOL;
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
