<?php
/** @var \App\Core\ResolvedTenant $tenant */

$base = \App\Core\Url::base('');
$link = $base . $tenant->urlPrefix() . '/book';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Afiliado</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Afiliado</h1>
                <p class="page-subtitle">Compartilhe seu link e traga novos clientes para seu negócio.</p>
            </div>
        </div>

        <div class="card">
            <div class="small">Seu link para agendamento:</div>
            <div style="margin-top:8px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="text" value="<?php echo htmlspecialchars($link); ?>" readonly style="min-width:280px; flex:1;">
                <button class="btn" type="button" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($link, ENT_QUOTES); ?>').then(function(){alert('Link copiado');});">Copiar</button>
                <a class="btn" href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener">Abrir</a>
            </div>
            <div class="small" style="margin-top:10px;">Dica: você pode colocar este link na bio do Instagram, WhatsApp ou site.</div>
        </div>
    </div>
</body>
</html>
