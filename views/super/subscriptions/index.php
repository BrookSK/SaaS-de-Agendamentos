<?php
/** @var array<int, array<string,mixed>> $subscriptions */
/** @var array<int, \App\Models\Tenant> $tenants */
/** @var array<int, \App\Models\Plan> $plans */
/** @var string|null $message */
/** @var string|null $error */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Assinaturas</title>
</head>
<body>
    <h1>Assinaturas</h1>

    <p><a href="/super/dashboard">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color: #070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: #b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <h2>Definir/Atualizar assinatura de tenant</h2>
    <form method="post" action="/super/subscriptions">
        <div>
            <label>Empresa</label><br>
            <select name="tenant_id" required>
                <option value="">Selecione</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?php echo (int)$t->id; ?>"><?php echo htmlspecialchars($t->name . ' (' . $t->slug . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>Plano</label><br>
            <select name="plan_id" required>
                <option value="">Selecione</option>
                <?php foreach ($plans as $p): ?>
                    <option value="<?php echo (int)$p->id; ?>"><?php echo htmlspecialchars($p->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>Status</label><br>
            <select name="status">
                <option value="trial">Trial</option>
                <option value="active">Ativa</option>
                <option value="past_due">Inadimplente</option>
                <option value="blocked">Bloqueada</option>
                <option value="canceled">Cancelada</option>
            </select>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <h2>Lista</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Empresa</th>
                <th>Plano</th>
                <th>Status</th>
                <th>Início</th>
                <th>Renova</th>
                <th>ASAAS Customer</th>
                <th>ASAAS Subscription</th>
                <th>ASAAS Último status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscriptions as $s): ?>
                <tr>
                    <td><?php echo (int)$s['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$s['tenant_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['plan_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$s['status']); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['started_at'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['renews_at'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['asaas_customer_id'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['asaas_subscription_id'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($s['asaas_last_status'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
