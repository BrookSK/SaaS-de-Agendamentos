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
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="h1">Recuperar senha</div>

            <?php if (!empty($message)): ?>
                <div class="notice"><strong><?php echo htmlspecialchars($message); ?></strong></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($action); ?>">
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <button type="submit">Enviar link</button>
                </div>
            </form>

            <div class="footer-links">
                <a href="<?php echo htmlspecialchars($prefix . '/login'); ?>">Voltar ao login</a>
            </div>
        </div>
    </div>
</body>
</html>
