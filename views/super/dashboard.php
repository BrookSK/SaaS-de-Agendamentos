<?php /** @var array|null $user */ ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Dashboard</title>
</head>
<body>
    <h1>Dashboard - Super Admin</h1>

    <p>Logado como: <strong><?php echo htmlspecialchars($user['email'] ?? ''); ?></strong></p>

    <form method="post" action="/logout">
        <button type="submit">Sair</button>
    </form>

    <h2>Próximos módulos</h2>
    <ul>
        <li><a href="/super/tenants">Empresas (Tenants)</a></li>
        <li><a href="/super/plans">Planos</a></li>
        <li><a href="/super/subscriptions">Assinaturas</a></li>
        <li><a href="/super/settings">Configurações (SMTP / White Label)</a></li>
        <li><a href="/super/audit">Auditoria (logs)</a></li>
        <li><a href="/super/asaas">Integração Asaas</a></li>
        <li><a href="/super/webhooks">Webhooks (Global)</a></li>
    </ul>
</body>
</html>
