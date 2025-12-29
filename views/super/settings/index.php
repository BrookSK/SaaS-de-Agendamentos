<?php
/** @var array<string, string|null> $settings */
/** @var string|null $message */
/** @var string|null $error */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Configurações</title>
</head>
<body>
    <h1>Configurações Globais</h1>

    <p><a href="/super/dashboard">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color: #070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: #b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="/super/settings" enctype="multipart/form-data">
        <h2>White Label</h2>
        <div>
            <label>Nome do sistema</label><br>
            <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system.name'] ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Descrição (SEO)</label><br>
            <textarea name="system_description" rows="3" cols="60"><?php echo htmlspecialchars($settings['system.description'] ?? ''); ?></textarea>
        </div>

        <div style="margin-top: 8px;">
            <label>Logo do sistema</label><br>
            <input type="file" name="branding_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            <?php $logoPath = (string)($settings['branding.logo_path'] ?? ''); ?>
            <?php if ($logoPath !== ''): ?>
                <div style="margin-top: 8px;" class="small">Atual:</div>
                <div style="margin-top: 6px;"><img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" style="max-height:44px;max-width:220px;display:block"></div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 8px;">
            <label>Favicon</label><br>
            <input type="file" name="branding_favicon" accept="image/x-icon,image/png,image/jpeg,image/webp,image/svg+xml">
            <?php $faviconPath = (string)($settings['branding.favicon_path'] ?? ''); ?>
            <?php if ($faviconPath !== ''): ?>
                <div style="margin-top: 8px;" class="small">Atual:</div>
                <div style="margin-top: 6px;"><img src="<?php echo htmlspecialchars($faviconPath); ?>" alt="Favicon" style="height:22px;width:22px;display:block"></div>
            <?php endif; ?>
        </div>

        <h2 style="margin-top: 16px;">SMTP</h2>
        <div>
            <label>Host</label><br>
            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp.host'] ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Porta</label><br>
            <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp.port'] ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Criptografia</label><br>
            <select name="smtp_encryption">
                <?php $enc = $settings['smtp.encryption'] ?? 'none'; ?>
                <option value="none" <?php echo $enc === 'none' ? 'selected' : ''; ?>>Nenhuma</option>
                <option value="tls" <?php echo $enc === 'tls' ? 'selected' : ''; ?>>TLS (STARTTLS)</option>
                <option value="ssl" <?php echo $enc === 'ssl' ? 'selected' : ''; ?>>SSL</option>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>Usuário</label><br>
            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp.username'] ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Senha</label><br>
            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp.password'] ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>From e-mail</label><br>
            <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp.from_email'] ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>From nome</label><br>
            <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp.from_name'] ?? ''); ?>">
        </div>

        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <h2 style="margin-top: 20px;">Testar envio</h2>
    <form method="post" action="/super/settings/test-email">
        <div>
            <label>Enviar para</label><br>
            <input type="email" name="to" required>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Enviar teste</button>
        </div>
    </form>
</body>
</html>
