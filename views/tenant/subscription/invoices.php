<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<string,mixed>|null $subscription */
/** @var array<int, array<string,mixed>> $payments */

$prefix = $tenant->urlPrefix();
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faturas</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Faturas</h1>
                <p class="page-subtitle">Pagamentos vinculados à sua assinatura.</p>
            </div>
            <div class="page-meta">
                <a class="btn" href="<?php echo htmlspecialchars($prefix . '/subscription'); ?>">Voltar</a>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Provedor</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Pago em</th>
                        <th>Criado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?php echo (int)$p['id']; ?></td>
                            <td><?php echo htmlspecialchars((string)$p['provider']); ?></td>
                            <td>R$ <?php echo number_format(((int)$p['amount_cents']) / 100, 2, ',', '.'); ?></td>
                            <td><span class="badge"><?php echo htmlspecialchars((string)$p['status']); ?></span></td>
                            <td><?php echo htmlspecialchars((string)($p['paid_at'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($p['created_at'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($payments === []): ?>
                <div class="small" style="margin-top:10px;">Nenhuma fatura encontrada ainda.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
