<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var string $title */
/** @var string $subtitle */

$prefix = $tenant->urlPrefix();
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title"><?php echo htmlspecialchars($title); ?></h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
            </div>
            <div class="page-meta">
                <a class="btn" href="<?php echo htmlspecialchars($prefix . '/dashboard'); ?>">Painel</a>
            </div>
        </div>

        <div class="card">
            <div class="small">Página pronta para receber implementação. Layout e navegação já integrados ao menu.</div>
        </div>
    </div>
</body>
</html>
