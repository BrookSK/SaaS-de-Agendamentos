<?php
/** @var \App\Core\ResolvedTenant|null $tenant */
/** @var string|null $error */

$action = ($tenant?->urlPrefix() ?? '') . '/login';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="h1">Login</div>

    <?php if ($tenant !== null): ?>
        <p>Empresa (tenant): <strong><?php echo htmlspecialchars($tenant->slug); ?></strong></p>
    <?php else: ?>
        <p>Contexto: <strong>Super Admin</strong></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: #b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($action); ?>">
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <label>Senha</label>
                    <input type="password" name="password" required>
                </div>
                <div class="field">
                    <button type="submit">Entrar</button>
                </div>
            </form>

            <div class="footer-links">
                <a href="<?php echo htmlspecialchars(($tenant?->urlPrefix() ?? '') . '/forgot-password'); ?>">Esqueci minha senha</a>
                <a href="/">Voltar</a>
            </div>
        </div>
    </div>
</body>
</html>
