<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $startsAt */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agendamento confirmado</title>
</head>
<body>
    <h1>Agendamento solicitado!</h1>
    <p>Empresa: <strong><?php echo htmlspecialchars($tenant->slug); ?></strong></p>
    <p>Data/hora: <strong><?php echo htmlspecialchars($startsAt); ?></strong></p>

    <p>
        <a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/book'); ?>">Fazer outro agendamento</a>
    </p>
</body>
</html>
