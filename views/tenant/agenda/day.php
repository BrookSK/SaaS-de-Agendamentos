<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $day */
/** @var array<int, array<string,mixed>> $appointments */
/** @var array<int, \App\Models\Service> $services */
/** @var array<int, \App\Models\Employee> $employees */
/** @var array<int, \App\Models\Client> $clients */

$action = $tenant->urlPrefix() . '/agenda';
$error = $_GET['error'] ?? null;
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda (dia)</title>
</head>
<body>
    <h1>Agenda do dia</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <?php if (!empty($error)): ?>
        <p style="color:#b00;"><strong><?php echo htmlspecialchars((string)$error); ?></strong></p>
    <?php endif; ?>

    <form method="get" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/agenda'); ?>">
        <label>Dia:</label>
        <input type="date" name="day" value="<?php echo htmlspecialchars($day); ?>">
        <button type="submit">Ver</button>
    </form>

    <h2>Novo agendamento</h2>
    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <input type="hidden" name="day" value="<?php echo htmlspecialchars($day); ?>">

        <div>
            <label>Horário</label><br>
            <input type="time" name="time" value="09:00" required>
        </div>

        <div style="margin-top: 8px;">
            <label>Duração (min)</label><br>
            <input type="number" name="duration_minutes" min="1" value="30" required>
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

        <div style="margin-top: 8px;">
            <label>Serviço</label><br>
            <select name="service_id" required>
                <option value="">Selecione</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?php echo (int)$s->id; ?>"><?php echo htmlspecialchars($s->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top: 8px;">
            <label>Cliente</label><br>
            <select name="client_id" required>
                <option value="">Selecione</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?php echo (int)$c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top: 12px;">
            <button type="submit">Agendar</button>
        </div>
    </form>

    <h2>Agendamentos</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Início</th>
                <th>Fim</th>
                <th>Profissional</th>
                <th>Serviço</th>
                <th>Cliente</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$a['starts_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['ends_at']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['service_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['client_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$a['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
