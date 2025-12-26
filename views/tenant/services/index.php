<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Service> $services */

$action = $tenant->urlPrefix() . '/services';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Serviços</title>
</head>
<body>
    <h1>Serviços</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <h2>Novo serviço</h2>
    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Duração (min)</label><br>
            <input type="number" name="duration_minutes" min="1" value="30" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Preço (centavos)</label><br>
            <input type="number" name="price_cents" min="0" value="0" required>
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
                <th>Duração</th>
                <th>Preço</th>
                <th>Ativo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><?php echo (int)$s->id; ?></td>
                    <td><?php echo htmlspecialchars($s->name); ?></td>
                    <td><?php echo (int)$s->durationMinutes; ?> min</td>
                    <td>R$ <?php echo number_format($s->priceCents / 100, 2, ',', '.'); ?></td>
                    <td><?php echo (int)$s->active === 1 ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
