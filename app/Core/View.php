<?php

declare(strict_types=1);

namespace App\Core;

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

        if ((str_starts_with($view, 'auth/') || str_starts_with($view, 'public/') || str_starts_with($view, 'home/')) && stripos($html, '<body') !== false) {
            $shellStart = '<body class="guest">'
                . '<header class="guest-header">'
                . '<div class="guest-brand">Agenda SaaS</div>'
                . '</header>'
                . '<main class="guest-main">'
                . '<div class="guest-card">';

            $shellEnd = '</div>'
                . '</main>'
                . '<footer class="guest-footer">© ' . date('Y') . ' Agenda SaaS</footer>'
                . '</body>';

            $html = preg_replace('/<body(\b[^>]*)>/i', $shellStart, $html, 1) ?? $html;
            $html = preg_replace('/<\/body>/i', $shellEnd, $html, 1) ?? $html;
        }

        if ((str_starts_with($view, 'super/') || str_starts_with($view, 'tenant/')) && stripos($html, '<body') !== false) {
            $u = Auth::user();
            $role = (string)($u['role'] ?? '');
            $userEmail = (string)($u['email'] ?? '');
            $prefix = Tenant::current()?->urlPrefix() ?? '';

            $navLinks = '';
            if ($role === 'super_admin') {
                $navLinks .= '<a class="nav-item" href="/super/dashboard">Dashboard</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/tenants">Empresas</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/plans">Planos</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/subscriptions">Assinaturas</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/webhooks">Webhooks</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/audit">Auditoria</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/settings">Configurações</a>\n';
                $navLinks .= '<a class="nav-item" href="/super/asaas">Asaas</a>\n';
            } else {
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/dashboard') . '">Dashboard</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/agenda') . '">Agenda</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/services') . '">Serviços</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/employees') . '">Profissionais</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/clients') . '">Clientes</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/finance') . '">Financeiro</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/reports') . '">Relatórios</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/settings/notifications') . '">Notificações</a>\n';
                $navLinks .= '<a class="nav-item" href="' . htmlspecialchars($prefix . '/audit') . '">Auditoria</a>\n';
            }

            $logoutAction = $role === 'super_admin' ? '/logout' : ($prefix . '/logout');

            $shellStart = '<body class="app">'
                . '<header class="app-header">'
                . '<div class="app-brand">Agenda SaaS</div>'
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
                . '<footer class="app-footer">© ' . date('Y') . ' Agenda SaaS</footer>'
                . '<script>(function(){function closeNav(){document.body.classList.remove("nav-open");}document.addEventListener("click",function(e){var nav=document.querySelector(".app-nav");var btn=document.querySelector(".app-menu-btn");if(!nav||!btn){return;}if(document.body.classList.contains("nav-open")&&!nav.contains(e.target)&&!btn.contains(e.target)){closeNav();}});})();</script>'
                . '</body>';

            $html = preg_replace('/<body(\b[^>]*)>/i', $shellStart, $html, 1) ?? $html;
            $html = preg_replace('/<\/body>/i', $shellEnd, $html, 1) ?? $html;
        }

        return $html;
    }
}
