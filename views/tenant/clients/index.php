<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Client> $clients */

$action = $tenant->urlPrefix() . '/clients';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes</title>
</head>
<body>
    <h1>Clientes</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <h2>Novo cliente</h2>
    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Telefone</label><br>
            <input type="text" name="phone">
        </div>
        <div style="margin-top: 8px;">
            <label>E-mail</label><br>
            <input type="email" name="email">
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
                <th>Telefone</th>
                <th>E-mail</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?php echo (int)$c->id; ?></td>
                    <td><?php echo htmlspecialchars($c->name); ?></td>
                    <td><?php echo htmlspecialchars($c->phone ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($c->email ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
