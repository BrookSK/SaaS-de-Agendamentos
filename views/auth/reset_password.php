<?php
/** @var \App\Core\ResolvedTenant|null $tenant */
/** @var string $token */
/** @var string|null $error */

$prefix = $tenant?->urlPrefix() ?? '';
$action = $prefix . '/reset-password';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redefinir senha</title>
</head>
<body>
    <h1>Redefinir senha</h1>

    <?php if (!empty($error)): ?>
        <p style="color:#b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($action); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div>
            <label>Nova senha</label><br>
            <input type="password" name="password" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Confirmar nova senha</label><br>
            <input type="password" name="password_confirm" required>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>
</body>
</html>
