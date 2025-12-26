<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Service> $services */
/** @var array<int, \App\Models\Employee> $employees */
/** @var string|null $error */

$action = $tenant->urlPrefix() . '/book';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agendar</title>
</head>
<body>
    <h1>Agendamento Online</h1>
    <p>Empresa: <strong><?php echo htmlspecialchars($tenant->slug); ?></strong></p>

    <?php if (!empty($error)): ?>
        <p style="color:#b00;"><strong><?php echo htmlspecialchars((string)$error); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <h2>Dados do cliente</h2>
        <div>
            <label>Nome</label><br>
            <input type="text" name="client_name" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Telefone</label><br>
            <input type="text" name="client_phone">
        </div>
        <div style="margin-top: 8px;">
            <label>E-mail</label><br>
            <input type="email" name="client_email">
        </div>

        <h2 style="margin-top: 16px;">Serviço e profissional</h2>
        <div>
            <label>Serviço</label><br>
            <select name="service_id" required>
                <option value="">Selecione</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?php echo (int)$s->id; ?>"><?php echo htmlspecialchars($s->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top: 8px;">
            <label>Profissional</label><br>
            <select name="employee_id" required>
                <option value="">Selecione</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?php echo (int)$e->id; ?>"><?php echo htmlspecialchars($e->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <h2 style="margin-top: 16px;">Data e horário</h2>
        <div>
            <label>Dia</label><br>
            <input type="date" name="day" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Hora</label><br>
            <input type="time" name="time" value="09:00" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Duração (min)</label><br>
            <input type="number" name="duration_minutes" min="1" value="30" required>
        </div>

        <div style="margin-top: 12px;">
            <button type="submit">Confirmar agendamento</button>
        </div>
    </form>

    <p style="margin-top: 16px;">
        <a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Área da empresa</a>
    </p>
</body>
</html>
