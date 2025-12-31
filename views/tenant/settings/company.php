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
    'business_hours' => $prefix . '/settings/business-hours',
    'employee_hours' => $prefix . '/settings/employee-hours',
    'holidays' => $prefix . '/settings/holidays',
    'time_blocks' => $prefix . '/settings/time-blocks',
    'notifications' => $prefix . '/settings/notifications',
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
                        $active = $hrefPath === $uriPath;
                        if ($key === 'company' && str_starts_with($uriPath, $prefix . '/settings/company')) {
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

                    <form method="post" action="<?php echo htmlspecialchars($prefix . '/settings/company'); ?>">
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
                </div>
            </section>
        </div>
    </div>
</body>
</html>
