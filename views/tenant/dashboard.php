<?php
/** @var array|null $user */
/** @var \App\Core\ResolvedTenant $tenant */

$logoutAction = $tenant->urlPrefix() . '/logout';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Empresa - Dashboard</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Painel</h1>
                <p class="page-subtitle">Empresa: <strong><?php echo htmlspecialchars($tenant->slug); ?></strong></p>
            </div>
            <div class="page-meta">
                <span class="badge">tenant_id: <?php echo htmlspecialchars((string)($tenant->tenantId ?? 'null')); ?></span>
            </div>
        </div>

        <div class="grid">
            <div class="stat-card">
                <div class="stat-title">Agendamentos (hoje)</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/agenda'); ?>">Abrir agenda</a></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Serviços</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/services'); ?>">Gerenciar</a></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Profissionais</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/employees'); ?>">Gerenciar</a></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Clientes</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/clients'); ?>">Gerenciar</a></div>
            </div>
        </div>

        <div class="card section">
            <div class="section-title">Ações rápidas</div>
            <div class="quick-actions">
                <a class="qa" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/book'); ?>">Página pública de agendamento</a>
                <a class="qa" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance'); ?>">Financeiro</a>
                <a class="qa" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/reports'); ?>">Relatórios</a>
                <a class="qa" href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/business-hours'); ?>">Horário de funcionamento</a>
            </div>
        </div>
    </div>
</body>
</html>
