<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Employee> $employees */

$action = $tenant->urlPrefix() . '/employees';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Funcionários</title>
</head>
<body>
    <h1>Funcionários</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <h2>Novo funcionário</h2>
    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required>
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
                <th>Ativo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $e): ?>
                <tr>
                    <td><?php echo (int)$e->id; ?></td>
                    <td><?php echo htmlspecialchars($e->name); ?></td>
                    <td><?php echo (int)$e->active === 1 ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
