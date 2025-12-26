<?php /** @var \App\Core\ResolvedTenant|null $tenant */ ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SaaS de Agendamentos</title>
</head>
<body>
    <h1>SaaS de Agendamentos (MVC puro)</h1>

    <p>
        <?php if ($tenant === null): ?>
            Você está no contexto global.
        <?php else: ?>
            Tenant: <strong><?php echo htmlspecialchars($tenant->slug); ?></strong>
        <?php endif; ?>
    </p>

    <h2>Acessos rápidos</h2>
    <ul>
        <li><a href="/login">Login Super Admin</a></li>
        <li><a href="/t/demo/login">Login Empresa Demo</a></li>
        <li><a href="/super/dashboard">Dashboard Super Admin</a></li>
        <li><a href="/t/demo/dashboard">Dashboard Empresa Demo</a></li>
    </ul>
</body>
</html>
