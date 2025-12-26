<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Employee> $employees */
/** @var array<int, array<string,mixed>> $blocks */
/** @var string|null $message */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bloqueios</title>
</head>
<body>
    <h1>Bloqueios de agenda</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <h2>Novo bloqueio</h2>
    <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/time-blocks'); ?>">
        <div>
            <label>Profissional (opcional)</label><br>
            <select name="employee_id">
                <option value="">Todos</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?php echo (int)$e->id; ?>"><?php echo htmlspecialchars($e->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>Início (YYYY-MM-DD HH:MM:SS)</label><br>
            <input type="text" name="starts_at" placeholder="2026-01-01 09:00:00" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Fim (YYYY-MM-DD HH:MM:SS)</label><br>
            <input type="text" name="ends_at" placeholder="2026-01-01 12:00:00" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Motivo</label><br>
            <input type="text" name="reason" size="60">
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <h2>Lista</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Profissional</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blocks as $b): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)($b['employee_name'] ?? 'Todos')); ?></td>
                    <td><?php echo htmlspecialchars((string)$b['starts_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)$b['ends_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)($b['reason'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
