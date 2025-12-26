<?php
/** @var \App\Core\ResolvedTenant|null $tenant */
/** @var string|null $message */

$prefix = $tenant?->urlPrefix() ?? '';
$action = $prefix . '/forgot-password';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar senha</title>
</head>
<body>
    <h1>Recuperar senha</h1>

    <?php if (!empty($message)): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <div>
            <label>E-mail</label><br>
            <input type="email" name="email" required>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Enviar link</button>
        </div>
    </form>

    <p style="margin-top: 16px;">
        <a href="<?php echo htmlspecialchars($prefix . '/login'); ?>">Voltar ao login</a>
    </p>
</body>
</html>
