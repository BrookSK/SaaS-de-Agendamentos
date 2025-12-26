<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, array<string,mixed>> $hours */
/** @var string|null $message */

$map = [];
foreach ($hours as $h) {
    $map[(int)$h['weekday']] = $h;
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
    <title>Horário de funcionamento</title>
</head>
<body>
    <h1>Horário de funcionamento</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/business-hours'); ?>">
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>Dia</th>
                    <th>Ativo</th>
                    <th>Abertura</th>
                    <th>Fechamento</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($w = 0; $w <= 6; $w++): ?>
                    <?php
                        $row = $map[$w] ?? null;
                        $active = (int)($row['active'] ?? ($w >= 1 && $w <= 5 ? 1 : 0));
                        $open = substr((string)($row['open_time'] ?? '09:00:00'), 0, 5);
                        $close = substr((string)($row['close_time'] ?? '18:00:00'), 0, 5);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($names[$w]); ?></td>
                        <td>
                            <input type="checkbox" name="active_<?php echo $w; ?>" value="1" <?php echo $active === 1 ? 'checked' : ''; ?>>
                        </td>
                        <td><input type="time" name="open_<?php echo $w; ?>" value="<?php echo htmlspecialchars($open); ?>"></td>
                        <td><input type="time" name="close_<?php echo $w; ?>" value="<?php echo htmlspecialchars($close); ?>"></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div style="margin-top:12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>
</body>
</html>
