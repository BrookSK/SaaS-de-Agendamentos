<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var int $year */
/** @var int $month */
/** @var string $view */
/** @var string $day */
/** @var string|null $weekStart */
/** @var string|null $weekEnd */
/** @var array<int, array<string,mixed>> $appointments */

$prefix = $tenant->urlPrefix();

$view = isset($view) && is_string($view) ? $view : 'month';
$day = isset($day) && is_string($day) ? $day : date('Y-m-d');

$mk = static function (string $v, array $q = []) use ($prefix): string {
    $base = $prefix . '/calendars?view=' . rawurlencode($v);
    foreach ($q as $k => $val) {
        $base .= '&' . rawurlencode((string)$k) . '=' . rawurlencode((string)$val);
    }
    return $base;
};

$dayTs = strtotime($day);
$dayTs = $dayTs !== false ? $dayTs : time();
$prevDay = date('Y-m-d', $dayTs - 86400);
$nextDay = date('Y-m-d', $dayTs + 86400);
$prevWeek = date('Y-m-d', $dayTs - 7 * 86400);
$nextWeek = date('Y-m-d', $dayTs + 7 * 86400);

$firstTs = strtotime(sprintf('%04d-%02d-01', $year, $month));
$firstTs = $firstTs !== false ? $firstTs : time();

$monthNames = [
    1 => 'janeiro',
    2 => 'fevereiro',
    3 => 'março',
    4 => 'abril',
    5 => 'maio',
    6 => 'junho',
    7 => 'julho',
    8 => 'agosto',
    9 => 'setembro',
    10 => 'outubro',
    11 => 'novembro',
    12 => 'dezembro',
];
$monthName = $monthNames[(int)date('n', $firstTs)] ?? 'mês';
$monthTitle = $monthName . ' de ' . date('Y', $firstTs);

$title = $view === 'month'
    ? $monthTitle
    : ($view === 'day'
        ? ('Dia ' . date('d/m/Y', $dayTs))
        : ('Semana de ' . date('d/m', strtotime((string)$weekStart ?: $day)) . ' a ' . date('d/m', strtotime((string)$weekEnd ?: $day))));

$daysInMonth = (int)date('t', $firstTs);
$firstWeekday = (int)date('w', $firstTs); // 0=dom

$prevTs = strtotime(date('Y-m-01', $firstTs) . ' -1 month');
$nextTs = strtotime(date('Y-m-01', $firstTs) . ' +1 month');
$prevY = (int)date('Y', $prevTs !== false ? $prevTs : $firstTs);
$prevM = (int)date('n', $prevTs !== false ? $prevTs : $firstTs);
$nextY = (int)date('Y', $nextTs !== false ? $nextTs : $firstTs);
$nextM = (int)date('n', $nextTs !== false ? $nextTs : $firstTs);

$byDay = [];
foreach ($appointments as $a) {
    $d = substr((string)$a['starts_at'], 0, 10);
    if (!isset($byDay[$d])) {
        $byDay[$d] = [];
    }
    $byDay[$d][] = $a;
}

$today = date('Y-m-d');
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calendários</title>
</head>
<body>
    <div class="page">
        <div class="calendar-toolbar">
            <div class="calendar-left">
                <a class="btn" href="<?php echo htmlspecialchars($prefix . '/calendars?y=' . $prevY . '&m=' . $prevM); ?>" aria-label="Anterior">‹</a>
                <a class="btn" href="<?php echo htmlspecialchars($prefix . '/calendars?y=' . $nextY . '&m=' . $nextM); ?>" aria-label="Próximo">›</a>
                <a class="btn" href="<?php echo htmlspecialchars($prefix . '/calendars'); ?>">Hoje</a>
            </div>

            <div class="calendar-title"><?php echo htmlspecialchars($title); ?></div>

            <div class="calendar-right">
                <div class="calendar-view-toggle">
                    <a class="btn<?php echo $view === 'month' ? ' is-active' : ''; ?>" href="<?php echo htmlspecialchars($mk('month', ['y' => $year, 'm' => $month])); ?>">Mês</a>
                    <a class="btn<?php echo $view === 'week' ? ' is-active' : ''; ?>" href="<?php echo htmlspecialchars($mk('week', ['day' => $day])); ?>">Semana</a>
                    <a class="btn<?php echo $view === 'day' ? ' is-active' : ''; ?>" href="<?php echo htmlspecialchars($mk('day', ['day' => $day])); ?>">Dia</a>
                </div>
            </div>
        </div>

        <?php if ($view === 'day'): ?>
            <div class="calendar-toolbar" style="margin-top:12px;">
                <div class="calendar-left">
                    <a class="btn" href="<?php echo htmlspecialchars($mk('day', ['day' => $prevDay])); ?>" aria-label="Anterior">‹</a>
                    <a class="btn" href="<?php echo htmlspecialchars($mk('day', ['day' => date('Y-m-d')])); ?>">Hoje</a>
                    <a class="btn" href="<?php echo htmlspecialchars($mk('day', ['day' => $nextDay])); ?>" aria-label="Próximo">›</a>
                </div>
            </div>

            <div class="card" style="margin-top:12px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Início</th>
                            <th>Serviço</th>
                            <th>Cliente</th>
                            <th>Profissional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr((string)$a['starts_at'], 11, 5)); ?></td>
                                <td><?php echo htmlspecialchars((string)($a['service_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($a['client_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($a['employee_name'] ?? '')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($appointments === []): ?>
                    <div class="small" style="margin-top:10px;">Nenhum agendamento neste dia.</div>
                <?php endif; ?>
            </div>

        <?php elseif ($view === 'week'): ?>
            <div class="calendar-toolbar" style="margin-top:12px;">
                <div class="calendar-left">
                    <a class="btn" href="<?php echo htmlspecialchars($mk('week', ['day' => $prevWeek])); ?>" aria-label="Anterior">‹</a>
                    <a class="btn" href="<?php echo htmlspecialchars($mk('week', ['day' => date('Y-m-d')])); ?>">Esta semana</a>
                    <a class="btn" href="<?php echo htmlspecialchars($mk('week', ['day' => $nextWeek])); ?>" aria-label="Próximo">›</a>
                </div>
            </div>

            <div class="card" style="margin-top:12px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Início</th>
                            <th>Serviço</th>
                            <th>Cliente</th>
                            <th>Profissional</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('d/m', strtotime(substr((string)$a['starts_at'], 0, 10)))); ?></td>
                                <td><?php echo htmlspecialchars(substr((string)$a['starts_at'], 11, 5)); ?></td>
                                <td><?php echo htmlspecialchars((string)($a['service_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($a['client_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($a['employee_name'] ?? '')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($appointments === []): ?>
                    <div class="small" style="margin-top:10px;">Nenhum agendamento nesta semana.</div>
                <?php endif; ?>
            </div>

        <?php else: ?>

        <div class="calendar-grid">
            <div class="calendar-dow">dom.</div>
            <div class="calendar-dow">seg.</div>
            <div class="calendar-dow">ter.</div>
            <div class="calendar-dow">qua.</div>
            <div class="calendar-dow">qui.</div>
            <div class="calendar-dow">sex.</div>
            <div class="calendar-dow">sáb.</div>

            <?php for ($i = 0; $i < $firstWeekday; $i++): ?>
                <div class="calendar-cell is-empty"></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <?php
                $d = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $items = $byDay[$d] ?? [];
                $isToday = $d === $today;
                ?>
                <div class="calendar-cell<?php echo $isToday ? ' is-today' : ''; ?>">
                    <div class="calendar-cell-head">
                        <div class="calendar-daynum"><?php echo (int)$day; ?></div>
                    </div>

                    <?php if ($items !== []): ?>
                        <div class="calendar-events">
                            <?php foreach (array_slice($items, 0, 3) as $a): ?>
                                <?php
                                $time = substr((string)$a['starts_at'], 11, 5);
                                $label = $time . ' ' . (string)($a['service_name'] ?? '');
                                ?>
                                <div class="calendar-event">
                                    <span class="dot"></span>
                                    <span class="txt"><?php echo htmlspecialchars($label); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($items) > 3): ?>
                                <div class="calendar-more">+<?php echo (int)(count($items) - 3); ?> mais</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <?php endif; ?>

        <div style="margin-top:12px" class="small">
            Dica: para cadastrar agendamentos use <a href="<?php echo htmlspecialchars($prefix . '/agenda'); ?>">Agendamentos</a>.
        </div>
    </div>
</body>
</html>
