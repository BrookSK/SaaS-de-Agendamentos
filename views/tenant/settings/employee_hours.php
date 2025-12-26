<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Employee> $employees */
/** @var int $employeeId */
/** @var array<int, array<string,mixed>> $rows */
/** @var string|null $message */

$map = [];
foreach ($rows as $r) {
    $map[(int)$r['weekday']] = $r;
}

$names = [
    0 => 'Domingo',
    1 => 'Segunda',
    2 => 'Terça',
    3 => 'Quarta',
    4 => 'Quinta',
    5 => 'Sexta',
    6 => 'Sábado',
];
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disponibilidade do profissional</title>
</head>
<body>
    <h1>Disponibilidade do profissional</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="get" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/employee-hours'); ?>">
        <label>Profissional</label><br>
        <select name="employee_id" onchange="this.form.submit()">
            <?php foreach ($employees as $e): ?>
                <option value="<?php echo (int)$e->id; ?>" <?php echo (int)$e->id === (int)$employeeId ? 'selected' : ''; ?>><?php echo htmlspecialchars($e->name); ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit">Selecionar</button></noscript>
    </form>

    <?php if ($employeeId <= 0): ?>
        <p>Nenhum profissional cadastrado.</p>
    <?php else: ?>
        <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/employee-hours'); ?>">
            <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">

            <table border="1" cellpadding="6" cellspacing="0">
                <thead>
                    <tr>
                        <th>Dia</th>
                        <th>Ativo</th>
                        <th>Início</th>
                        <th>Fim</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($w = 0; $w <= 6; $w++): ?>
                        <?php
                            $row = $map[$w] ?? null;
                            $active = (int)($row['active'] ?? ($w >= 1 && $w <= 5 ? 1 : 0));
                            $start = substr((string)($row['start_time'] ?? '09:00:00'), 0, 5);
                            $end = substr((string)($row['end_time'] ?? '18:00:00'), 0, 5);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($names[$w]); ?></td>
                            <td>
                                <input type="checkbox" name="active_<?php echo $w; ?>" value="1" <?php echo $active === 1 ? 'checked' : ''; ?>>
                            </td>
                            <td><input type="time" name="start_<?php echo $w; ?>" value="<?php echo htmlspecialchars($start); ?>"></td>
                            <td><input type="time" name="end_<?php echo $w; ?>" value="<?php echo htmlspecialchars($end); ?>"></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div style="margin-top:12px;">
                <button type="submit">Salvar</button>
            </div>
        </form>

        <p style="margin-top:12px;">
            Obs: se você não configurar nada aqui, o sistema usa só o horário de funcionamento da empresa.
        </p>
    <?php endif; ?>
</body>
</html>
