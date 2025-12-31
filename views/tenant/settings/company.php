<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $tab */
/** @var \App\Models\Tenant|null $company */
/** @var string|null $message */
/** @var string|null $error */

$prefix = $tenant->urlPrefix();

$companyName = $company?->name ?? '';
$companyEmail = $company?->email ?? '';
$companyPhone = $company?->phone ?? '';
$companyCpfCnpj = $company?->cpfCnpj ?? '';

$tabs = [
    'company' => ['Dados da Empresa', 'Atualize os dados cadastrais da empresa.'],
    'business_hours' => ['Horário de Funcionamento', 'Defina horários e turnos.'],
    'employee_hours' => ['Horário por Profissional', 'Regras específicas por colaborador.'],
    'holidays' => ['Feriados', 'Configure feriados e folgas.'],
    'time_blocks' => ['Bloqueios', 'Bloqueios pontuais na agenda.'],
    'notifications' => ['Notificações', 'Mensagens e automações.'],
];

[$title, $subtitle] = $tabs[$tab] ?? $tabs['company'];

$mkUrl = static function (string $t) use ($prefix): string {
    return $prefix . '/settings/company?tab=' . rawurlencode($t);
};

$tabHrefs = [
    'company' => $mkUrl('company'),
    'business_hours' => $mkUrl('business_hours'),
    'employee_hours' => $mkUrl('employee_hours'),
    'holidays' => $mkUrl('holidays'),
    'time_blocks' => $mkUrl('time_blocks'),
    'notifications' => $mkUrl('notifications'),
];

$uriPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurações</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Configurações</h1>
                <p class="page-subtitle">Ajuste as configurações do seu negócio.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="notice success"><strong><?php echo htmlspecialchars((string)$message); ?></strong></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="notice error"><strong><?php echo htmlspecialchars((string)$error); ?></strong></div>
        <?php endif; ?>

        <div class="tenant-settings-layout">
            <aside class="tenant-settings-menu">
                <div class="tenant-settings-menu-title">Configurações da Empresa</div>
                <?php foreach ($tabs as $key => $meta): ?>
                    <?php $href = $tabHrefs[$key] ?? $mkUrl($key); ?>
                    <?php
                        $hrefPath = (string)(parse_url($href, PHP_URL_PATH) ?? $href);
                        $active = $key === $tab;
                        if ($key === 'company' && str_starts_with($uriPath, $prefix . '/settings/company') && $tab === 'company') {
                            $active = true;
                        }
                    ?>
                    <a class="tenant-settings-link<?php echo $active ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>">
                        <?php echo htmlspecialchars($meta[0]); ?>
                    </a>
                <?php endforeach; ?>
            </aside>

            <section class="tenant-settings-content">
                <div class="card">
                    <div class="tenant-settings-content-head">
                        <div>
                            <div class="tenant-settings-h"><?php echo htmlspecialchars($title); ?></div>
                            <div class="small"><?php echo htmlspecialchars($subtitle); ?></div>
                        </div>
                    </div>

                    <?php if ($tab === 'business_hours'): ?>
                        <?php /** @var array<int, array<string,mixed>> $hours */ ?>
                        <?php
                            $hours = isset($hours) && is_array($hours) ? $hours : [];
                            $map = [];
                            foreach ($hours as $h) {
                                $map[(int)($h['weekday'] ?? -1)] = $h;
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
                        <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
                            <input type="hidden" name="tab" value="business_hours">
                            <table class="table">
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
                                <button class="btn" type="submit">Salvar</button>
                            </div>
                        </form>

                    <?php elseif ($tab === 'employee_hours'): ?>
                        <?php /** @var array<int, \App\Models\Employee> $employees */ ?>
                        <?php /** @var int $employeeId */ ?>
                        <?php /** @var array<int, array<string,mixed>> $rows */ ?>
                        <?php
                            $employees = isset($employees) && is_array($employees) ? $employees : [];
                            $employeeId = isset($employeeId) ? (int)$employeeId : 0;
                            $rows = isset($rows) && is_array($rows) ? $rows : [];
                            $map = [];
                            foreach ($rows as $r) {
                                $map[(int)($r['weekday'] ?? -1)] = $r;
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

                        <form method="get" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
                            <input type="hidden" name="tab" value="employee_hours">
                            <label>Profissional</label><br>
                            <select name="employee_id" onchange="this.form.submit()">
                                <?php foreach ($employees as $e): ?>
                                    <option value="<?php echo (int)$e->id; ?>" <?php echo (int)$e->id === (int)$employeeId ? 'selected' : ''; ?>><?php echo htmlspecialchars($e->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <noscript><button class="btn" type="submit" style="margin-left:8px;">Selecionar</button></noscript>
                        </form>

                        <?php if ($employeeId <= 0): ?>
                            <div class="small" style="margin-top:10px;">Nenhum profissional cadastrado.</div>
                        <?php else: ?>
                            <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>" style="margin-top:12px;">
                                <input type="hidden" name="tab" value="employee_hours">
                                <input type="hidden" name="employee_id" value="<?php echo (int)$employeeId; ?>">
                                <table class="table">
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
                                    <button class="btn" type="submit">Salvar</button>
                                </div>
                                <div class="small" style="margin-top:10px;">
                                    Obs: se você não configurar nada aqui, o sistema usa só o horário de funcionamento da empresa.
                                </div>
                            </form>
                        <?php endif; ?>

                    <?php elseif ($tab === 'holidays'): ?>
                        <?php /** @var array<int, array<string,mixed>> $holidays */ ?>
                        <?php $holidays = isset($holidays) && is_array($holidays) ? $holidays : []; ?>

                        <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
                            <input type="hidden" name="tab" value="holidays">
                            <div>
                                <label>Dia</label><br>
                                <input type="date" name="day" required>
                            </div>
                            <div style="margin-top: 8px;">
                                <label>Nome</label><br>
                                <input type="text" name="name">
                            </div>
                            <div style="margin-top: 8px;">
                                <label>Fechado?</label><br>
                                <select name="closed">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>
                            <div style="margin-top: 12px;">
                                <button class="btn" type="submit">Salvar</button>
                            </div>
                        </form>

                        <div style="margin-top:14px;"></div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Dia</th>
                                    <th>Nome</th>
                                    <th>Fechado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($holidays as $h): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)$h['day']); ?></td>
                                        <td><?php echo htmlspecialchars((string)($h['name'] ?? '')); ?></td>
                                        <td><?php echo (int)($h['closed'] ?? 0) === 1 ? 'Sim' : 'Não'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if ($holidays === []): ?>
                            <div class="small" style="margin-top:10px;">Nenhum feriado configurado.</div>
                        <?php endif; ?>

                    <?php elseif ($tab === 'time_blocks'): ?>
                        <?php /** @var array<int, \App\Models\Employee> $employees */ ?>
                        <?php /** @var array<int, array<string,mixed>> $blocks */ ?>
                        <?php
                            $employees = isset($employees) && is_array($employees) ? $employees : [];
                            $blocks = isset($blocks) && is_array($blocks) ? $blocks : [];
                        ?>

                        <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
                            <input type="hidden" name="tab" value="time_blocks">
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
                                <button class="btn" type="submit">Salvar</button>
                            </div>
                        </form>

                        <div style="margin-top:14px;"></div>
                        <table class="table">
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
                                        <td><?php echo htmlspecialchars((string)($b['starts_at'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($b['ends_at'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($b['reason'] ?? '')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if ($blocks === []): ?>
                            <div class="small" style="margin-top:10px;">Nenhum bloqueio configurado.</div>
                        <?php endif; ?>

                    <?php elseif ($tab === 'notifications'): ?>
                        <?php /** @var array<int, string> $events */ ?>
                        <?php /** @var array<string, array<string,mixed>> $map */ ?>
                        <?php
                            $events = isset($events) && is_array($events) ? $events : [];
                            $map = isset($map) && is_array($map) ? $map : [];
                        ?>
                        <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
                            <input type="hidden" name="tab" value="notifications">
                            <div class="small" style="margin-bottom:10px;">
                                Você pode escolher quem recebe (cliente/funcionário/admin) e quais canais ficam ativos.
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Evento</th>
                                        <th>Cliente</th>
                                        <th>Funcionário</th>
                                        <th>Admin</th>
                                        <th>Email</th>
                                        <th>Webhook</th>
                                        <th>Subject</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <?php $r = $map[$event] ?? null; ?>
                                        <?php
                                            $notifyClient = (int)($r['notify_client'] ?? 0);
                                            $notifyEmployee = (int)($r['notify_employee'] ?? 0);
                                            $notifyAdmin = (int)($r['notify_admin'] ?? 1);

                                            $channels = [];
                                            if (isset($r['channels_json']) && $r['channels_json'] !== null) {
                                                $decoded = json_decode((string)$r['channels_json'], true);
                                                if (is_array($decoded)) {
                                                    $channels = $decoded;
                                                }
                                            }

                                            $channelEmail = (bool)($channels['email'] ?? false);
                                            $channelWebhook = (bool)($channels['webhook'] ?? true);

                                            $subject = (string)($r['template_subject'] ?? '');
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($event); ?></td>
                                            <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_notify_client'); ?>" value="1" <?php echo $notifyClient === 1 ? 'checked' : ''; ?>></td>
                                            <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_notify_employee'); ?>" value="1" <?php echo $notifyEmployee === 1 ? 'checked' : ''; ?>></td>
                                            <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_notify_admin'); ?>" value="1" <?php echo $notifyAdmin === 1 ? 'checked' : ''; ?>></td>
                                            <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_channel_email'); ?>" value="1" <?php echo $channelEmail ? 'checked' : ''; ?>></td>
                                            <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_channel_webhook'); ?>" value="1" <?php echo $channelWebhook ? 'checked' : ''; ?>></td>
                                            <td><input type="text" name="<?php echo htmlspecialchars($event . '_subject'); ?>" value="<?php echo htmlspecialchars($subject); ?>" size="30"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div style="margin-top:12px;">
                                <button class="btn" type="submit">Salvar</button>
                            </div>
                        </form>

                    <?php else: ?>
                        <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
                            <input type="hidden" name="tab" value="company">
                            <div>
                                <label>Nome da empresa</label><br>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($companyName); ?>" placeholder="<?php echo htmlspecialchars($tenant->slug); ?>" required>
                            </div>
                            <div>
                                <label>E-mail</label><br>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($companyEmail); ?>">
                            </div>
                            <div>
                                <label>Telefone</label><br>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($companyPhone); ?>">
                            </div>
                            <div>
                                <label>CPF/CNPJ</label><br>
                                <input type="text" name="cpf_cnpj" value="<?php echo htmlspecialchars($companyCpfCnpj); ?>">
                            </div>
                            <div style="margin-top:12px;">
                                <button class="btn" type="submit">Salvar</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
