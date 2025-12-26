<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $from */
/** @var string $to */
/** @var string|null $type */
/** @var string|null $status */
/** @var array{payable_open:int,receivable_open:int,net_open:int} $totals */
/** @var array<int, array<string,mixed>> $items */
/** @var string|null $message */
/** @var string|null $error */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contas a pagar/receber</title>
</head>
<body>
    <h1>Contas a pagar / receber</h1>

    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance'); ?>">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color:#b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <h2>Filtro</h2>
    <form method="get" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance/titles'); ?>">
        <label>De</label>
        <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>">
        <label>Até</label>
        <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>">
        <label>Tipo</label>
        <select name="type">
            <option value="" <?php echo $type === null ? 'selected' : ''; ?>>Todos</option>
            <option value="receivable" <?php echo $type === 'receivable' ? 'selected' : ''; ?>>A receber</option>
            <option value="payable" <?php echo $type === 'payable' ? 'selected' : ''; ?>>A pagar</option>
        </select>
        <label>Status</label>
        <select name="status">
            <option value="" <?php echo $status === null ? 'selected' : ''; ?>>Todos</option>
            <option value="open" <?php echo $status === 'open' ? 'selected' : ''; ?>>Aberto</option>
            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Pago</option>
            <option value="canceled" <?php echo $status === 'canceled' ? 'selected' : ''; ?>>Cancelado</option>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <h2>Resumo (em aberto)</h2>
    <p>A receber: <strong>R$ <?php echo number_format($totals['receivable_open'] / 100, 2, ',', '.'); ?></strong></p>
    <p>A pagar: <strong>R$ <?php echo number_format($totals['payable_open'] / 100, 2, ',', '.'); ?></strong></p>
    <p>Saldo: <strong>R$ <?php echo number_format($totals['net_open'] / 100, 2, ',', '.'); ?></strong></p>

    <h2>Novo título</h2>
    <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance/titles'); ?>">
        <div>
            <label>Tipo</label><br>
            <select name="type">
                <option value="receivable">A receber</option>
                <option value="payable">A pagar</option>
            </select>
        </div>
        <div style="margin-top:8px;">
            <label>Valor (centavos)</label><br>
            <input type="number" name="amount_cents" min="1" required>
        </div>
        <div style="margin-top:8px;">
            <label>Vencimento</label><br>
            <input type="date" name="due_on" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
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

    <h2>Lista</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Vencimento</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Valor</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Pago em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$it['due_on']); ?></td>
                    <td><?php echo htmlspecialchars((string)$it['type']); ?></td>
                    <td><?php echo htmlspecialchars((string)$it['status']); ?></td>
                    <td>R$ <?php echo number_format(((int)$it['amount_cents']) / 100, 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['category'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['description'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['paid_at'] ?? '')); ?></td>
                    <td>
                        <?php if ((string)$it['status'] === 'open'): ?>
                            <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance/titles/pay'); ?>" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                                <button type="submit">Baixar</button>
                            </form>
                            <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/finance/titles/cancel'); ?>" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                                <button type="submit">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
