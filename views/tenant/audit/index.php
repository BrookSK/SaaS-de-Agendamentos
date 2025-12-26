<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, array<string,mixed>> $items */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Auditoria</title>
</head>
<body>
    <h1>Auditoria</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Quando</th>
                <th>User</th>
                <th>Ação</th>
                <th>Entidade</th>
                <th>ID</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$it['created_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['user_id'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)$it['action']); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['entity'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['entity_id'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($it['ip'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
