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
</head>
<body>
    <h1>Login</h1>

    <?php if ($tenant !== null): ?>
        <p>Empresa (tenant): <strong><?php echo htmlspecialchars($tenant->slug); ?></strong></p>
    <?php else: ?>
        <p>Contexto: <strong>Super Admin</strong></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: #b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <div>
            <label>E-mail</label><br>
            <input type="email" name="email" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Senha</label><br>
            <input type="password" name="password" required>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Entrar</button>
        </div>
    </form>

    <p style="margin-top: 12px;">
        <a href="<?php echo htmlspecialchars(($tenant?->urlPrefix() ?? '') . '/forgot-password'); ?>">Esqueci minha senha</a>
    </p>

    <p style="margin-top: 16px;">
        <a href="/">Voltar</a>
    </p>
</body>
</html>
