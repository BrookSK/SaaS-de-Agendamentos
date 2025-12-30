<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var int $year */
/** @var int $month */
/** @var array<int, array<string,mixed>> $appointments */

$prefix = $tenant->urlPrefix();

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

            <div class="calendar-title"><?php echo htmlspecialchars($monthTitle); ?></div>

            <div class="calendar-right">
                <div class="calendar-view-toggle">
                    <button type="button" class="btn is-active">Mês</button>
                    <button type="button" class="btn" disabled>Semana</button>
                    <button type="button" class="btn" disabled>Dia</button>
                </div>
            </div>
        </div>

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

        <div style="margin-top:12px" class="small">
            Dica: para cadastrar agendamentos use <a href="<?php echo htmlspecialchars($prefix . '/agenda'); ?>">Agendamentos</a>.
        </div>
    </div>
</body>
</html>
