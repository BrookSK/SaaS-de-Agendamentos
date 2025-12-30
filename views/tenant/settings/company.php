<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $tab */
/** @var string|null $message */
/** @var string|null $error */

$prefix = $tenant->urlPrefix();

$tabs = [
    'company' => ['Configurações da Empresa', 'Envie logotipo e banners.'],
    'general' => ['Configurações Gerais', 'Dados e preferências gerais.'],
    'appearance' => ['Aparência', 'Tema, cores e fontes.'],
    'business_hours' => ['Horário de Funcionamento', 'Defina horários e turnos.'],
    'holidays' => ['Feriados', 'Configure feriados e folgas.'],
    'embed' => ['Código Incorporado', 'Copie e cole no seu site.'],
    'qr' => ['Código QR', 'Baixe o QR para divulgação.'],
    'payments' => ['Configurações de Pagamento', 'Integrações e recebimentos.'],
    'notifications' => ['Notificações', 'Mensagens e automações.'],
];

[$title, $subtitle] = $tabs[$tab] ?? $tabs['company'];

$mkUrl = static function (string $t) use ($prefix): string {
    return $prefix . '/settings/company?tab=' . rawurlencode($t);
};
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
                    <?php $active = $key === $tab; ?>
                    <a class="tenant-settings-link<?php echo $active ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($mkUrl($key)); ?>">
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

                    <?php if ($tab === 'company'): ?>
                        <div class="tenant-upload-grid">
                            <div class="tenant-upload-box">
                                <div class="tenant-upload-preview"></div>
                                <div class="tenant-upload-row">
                                    <input type="text" value="Enviar Logotipo" disabled>
                                    <button class="btn" type="button" disabled>Browse</button>
                                </div>
                                <div class="small">Para uma melhor visualização, use 300 × 150px</div>
                                <div class="tenant-radio-row">
                                    <label><input type="radio" checked> Logotipo pequeno</label>
                                    <label><input type="radio"> Logotipo médio</label>
                                    <label><input type="radio"> Logotipo grande</label>
                                </div>
                            </div>

                            <div class="tenant-upload-box">
                                <div class="tenant-upload-preview is-wide"></div>
                                <div class="tenant-upload-row">
                                    <input type="text" value="Imagem do Banner" disabled>
                                    <button class="btn" type="button" disabled>Browse</button>
                                </div>
                                <div class="small">Para uma melhor visualização, use 1600 × 1000px</div>
                            </div>

                            <div class="tenant-upload-box">
                                <div class="tenant-upload-preview is-wide is-drop">Carregar Sobre a Imagem</div>
                                <div class="tenant-upload-row">
                                    <input type="text" value="Carregar Sobre a Imagem" disabled>
                                    <button class="btn" type="button" disabled>Browse</button>
                                </div>
                                <div class="small">Para uma melhor visualização, use 1200px +</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="tenant-settings-placeholder">
                            <div class="small">Essa seção terá suas regras específicas. Estrutura pronta para implementar.</div>
                            <div style="margin-top:12px">
                                <a class="btn" href="<?php echo htmlspecialchars($mkUrl('company')); ?>">Voltar para Configurações da Empresa</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
