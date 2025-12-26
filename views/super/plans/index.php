<?php
/** @var array<int, \App\Models\Plan> $plans */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Planos</title>
</head>
<body>
    <h1>Planos</h1>

    <p><a href="/super/dashboard">Voltar</a></p>

    <h2>Criar plano</h2>
    <form method="post" action="/super/plans">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Descrição</label><br>
            <textarea name="description" rows="3" cols="50"></textarea>
        </div>
        <div style="margin-top: 8px;">
            <label>Preço (centavos)</label><br>
            <input type="number" name="price_cents" min="0" value="0" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Ciclo</label><br>
            <select name="billing_cycle">
                <option value="monthly">Mensal</option>
                <option value="semiannual">Semestral</option>
                <option value="annual">Anual</option>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>Ativo</label><br>
            <select name="active">
                <option value="1">Sim</option>
                <option value="0">Não</option>
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
                <th>Nome</th>
                <th>Ciclo</th>
                <th>Preço</th>
                <th>Ativo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($plans as $p): ?>
                <tr>
                    <td><?php echo (int)$p->id; ?></td>
                    <td><?php echo htmlspecialchars($p->name); ?></td>
                    <td><?php echo htmlspecialchars($p->billingCycle); ?></td>
                    <td>R$ <?php echo number_format($p->priceCents / 100, 2, ',', '.'); ?></td>
                    <td><?php echo (int)$p->active === 1 ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
