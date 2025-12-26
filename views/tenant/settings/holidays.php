<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, array<string,mixed>> $holidays */
/** @var string|null $message */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feriados</title>
</head>
<body>
    <h1>Feriados</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <h2>Adicionar/Atualizar</h2>
    <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/holidays'); ?>">
        <div>
            <label>Dia</label><br>
            <input type="date" name="day" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Nome</label><br>
            <input type="text" name="name">
        </div>
        <div style="margin-top: 8px;">
            <label>Fechado?</label><br>
            <select name="closed">
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
                <th>Dia</th>
                <th>Nome</th>
                <th>Fechado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($holidays as $h): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$h['day']); ?></td>
                    <td><?php echo htmlspecialchars((string)($h['name'] ?? '')); ?></td>
                    <td><?php echo (int)$h['closed'] === 1 ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
