<?php /** @var array|null $user */ ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Dashboard</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Painel</h1>
                <p class="page-subtitle">Super Admin</p>
            </div>
        </div>

        <div class="grid">
            <div class="stat-card">
                <div class="stat-title">Empresas</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="/super/tenants">Gerenciar</a></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Planos</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="/super/plans">Ver</a></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Assinaturas</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="/super/subscriptions">Acompanhar</a></div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Webhooks</div>
                <div class="stat-value">—</div>
                <div class="stat-actions"><a class="link" href="/super/webhooks">Configurar</a></div>
            </div>
        </div>

        <div class="card section">
            <div class="section-title">Ações rápidas</div>
            <div class="quick-actions">
                <a class="qa" href="/super/tenants">Cadastrar/editar empresas</a>
                <a class="qa" href="/super/settings">Configurações do sistema</a>
                <a class="qa" href="/super/audit">Auditoria</a>
                <a class="qa" href="/super/asaas">Integração Asaas</a>
            </div>
        </div>
    </div>
</body>
</html>
