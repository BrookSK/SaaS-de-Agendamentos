<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, \App\Models\Location> $locations */
/** @var string|null $message */
/** @var string|null $error */

$action = $tenant->urlPrefix() . '/locations';
$deleteAction = $tenant->urlPrefix() . '/locations/delete';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Localizações</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Localizações</h1>
                <p class="page-subtitle">Cadastre endereços/unidades para atender seus clientes.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="notice success"><strong><?php echo htmlspecialchars((string)$message); ?></strong></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="notice error"><strong><?php echo htmlspecialchars((string)$error); ?></strong></div>
        <?php endif; ?>

        <div class="card">
            <h2 style="margin-top:0">Nova localização</h2>
            <form method="post" action="<?php echo htmlspecialchars($action); ?>">
                <div>
                    <label>Nome</label><br>
                    <input type="text" name="name" required>
                </div>
                <div>
                    <label>Endereço</label><br>
                    <input type="text" name="address">
                </div>
                <div>
                    <label>Telefone</label><br>
                    <input type="text" name="phone">
                </div>
                <div style="margin-top:12px;">
                    <button class="btn" type="submit">Salvar</button>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top:16px;">
            <h2 style="margin-top:0">Lista</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Endereço</th>
                        <th>Telefone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $l): ?>
                        <tr>
                            <td><?php echo (int)$l->id; ?></td>
                            <td><?php echo htmlspecialchars($l->name); ?></td>
                            <td><?php echo htmlspecialchars($l->address ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($l->phone ?? ''); ?></td>
                            <td>
                                <form method="post" action="<?php echo htmlspecialchars($deleteAction); ?>" onsubmit="return confirm('Remover esta localização?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo (int)$l->id; ?>">
                                    <button class="btn" type="submit">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
