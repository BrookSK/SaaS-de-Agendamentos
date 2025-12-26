<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $from */
/** @var string $to */
/** @var array<int, array<string,mixed>> $appointments */
/** @var array{in_cents:int,out_cents:int,balance_cents:int} $financeTotals */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatórios</title>
</head>
<body>
    <h1>Relatórios</h1>

    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <h2>Período</h2>
    <form method="get" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/reports'); ?>">
        <label>De</label>
        <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>">
        <label>Até</label>
        <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>">
        <button type="submit">Filtrar</button>
    </form>

    <h2>Exportar CSV</h2>
    <ul>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/reports/appointments.csv?from=' . rawurlencode($from) . '&to=' . rawurlencode($to)); ?>">Agendamentos</a></li>
        <li><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/reports/finance.csv?from=' . rawurlencode($from) . '&to=' . rawurlencode($to)); ?>">Financeiro (lançamentos)</a></li>
    </ul>

    <h2>Resumo Financeiro</h2>
    <p>Entradas: <strong>R$ <?php echo number_format($financeTotals['in_cents'] / 100, 2, ',', '.'); ?></strong></p>
    <p>Saídas: <strong>R$ <?php echo number_format($financeTotals['out_cents'] / 100, 2, ',', '.'); ?></strong></p>
    <p>Saldo: <strong>R$ <?php echo number_format($financeTotals['balance_cents'] / 100, 2, ',', '.'); ?></strong></p>

    <h2>Agendamentos (<?php echo count($appointments); ?>)</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Início</th>
                <th>Fim</th>
                <th>Profissional</th>
                <th>Serviço</th>
                <th>Cliente</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$a['starts_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['ends_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['service_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['client_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
