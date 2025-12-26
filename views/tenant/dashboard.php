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
    <h1>Dashboard - Empresa</h1>

    <p>
        Tenant: <strong><?php echo htmlspecialchars($tenant->slug); ?></strong>
        (tenant_id: <strong><?php echo htmlspecialchars((string)($tenant->tenantId ?? 'null')); ?></strong>)
    </p>

    <p>Logado como: <strong><?php echo htmlspecialchars($user['email'] ?? ''); ?></strong></p>

    <form method="post" action="<?php echo htmlspecialchars($logoutAction); ?>">
        <button type="submit">Sair</button>
    </form>

    <h2>Próximos módulos do tenant</h2>
    <ul>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/services'); ?>">Serviços</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/employees'); ?>">Funcionários</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/clients'); ?>">Clientes</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/agenda'); ?>">Agenda (dia)</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/book'); ?>">Página pública de agendamento</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/business-hours'); ?>">Configurar horário de funcionamento</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/employee-hours'); ?>">Configurar disponibilidade dos profissionais</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/holidays'); ?>">Configurar feriados</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/time-blocks'); ?>">Bloqueios de agenda</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/notifications'); ?>">Notificações (destinatários/templates)</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance'); ?>">Financeiro</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/reports'); ?>">Relatórios</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/audit'); ?>">Auditoria (logs)</a></li>
    </ul>

    <p style="margin-top: 16px;">
        <a href="/">Voltar</a>
    </p>
</body>
</html>
