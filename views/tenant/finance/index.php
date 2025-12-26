<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $from */
/** @var string $to */
/** @var array{in_cents:int,out_cents:int,balance_cents:int} $totals */
/** @var array<int, array<string,mixed>> $items */
/** @var string|null $message */
/** @var string|null $error */

$action = $tenant->urlPrefix() . '/finance';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financeiro</title>
</head>
<body>
    <h1>Financeiro</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance/titles'); ?>">Contas a pagar / receber</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color:#b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <h2>Período</h2>
    <form method="get" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance'); ?>">
        <label>De</label>
        <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>">
        <label>Até</label>
        <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>">
        <button type="submit">Filtrar</button>
    </form>

    <h2>Resumo</h2>
    <p>Entradas: <strong>R$ <?php echo number_format($totals['in_cents'] / 100, 2, ',', '.'); ?></strong></p>
    <p>Saídas: <strong>R$ <?php echo number_format($totals['out_cents'] / 100, 2, ',', '.'); ?></strong></p>
    <p>Saldo: <strong>R$ <?php echo number_format($totals['balance_cents'] / 100, 2, ',', '.'); ?></strong></p>

    <h2>Novo lançamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <div>
            <label>Tipo</label><br>
            <select name="type">
                <option value="in">Entrada</option>
                <option value="out">Saída</option>
            </select>
        </div>
        <div style="margin-top:8px;">
            <label>Valor (centavos)</label><br>
            <input type="number" name="amount_cents" min="1" required>
        </div>
        <div style="margin-top:8px;">
            <label>Data</label><br>
            <input type="date" name="occurred_on" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
        </div>
        <div style="margin-top:8px;">
            <label>Categoria</label><br>
            <input type="text" name="category">
        </div>
        <div style="margin-top:8px;">
            <label>Descrição</label><br>
            <input type="text" name="description" size="60">
        </div>
        <div style="margin-top:12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <h2>Movimentações</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Categoria</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$it['occurred_on']); ?></td>
                    <td><?php echo htmlspecialchars((string)$it['type']); ?></td>
                    <td>R$ <?php echo number_format(((int)$it['amount_cents']) / 100, 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['category'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['description'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
