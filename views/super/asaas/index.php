<?php
/** @var array<string, string|null> $settings */
/** @var array<int, array<string,mixed>> $events */
/** @var string|null $message */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - ASAAS</title>
</head>
<body>
    <h1>ASAAS</h1>

    <p><a href="/super/dashboard">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color: #070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <h2>Configuração</h2>
    <form method="post" action="/super/asaas">
        <div>
            <label>Ambiente</label><br>
            <?php $env = $settings['environment'] ?? 'sandbox'; ?>
            <select name="environment">
                <option value="sandbox" <?php echo $env === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                <option value="production" <?php echo $env === 'production' ? 'selected' : ''; ?>>Produção</option>
            </select>
        </div>

        <div style="margin-top: 8px;">
            <label>API Key Sandbox</label><br>
            <input type="text" name="api_key_sandbox" value="<?php echo htmlspecialchars($settings['api_key_sandbox'] ?? ''); ?>" size="80">
        </div>

        <div style="margin-top: 8px;">
            <label>API Key Produção</label><br>
            <input type="text" name="api_key_production" value="<?php echo htmlspecialchars($settings['api_key_production'] ?? ''); ?>" size="80">
        </div>

        <div style="margin-top: 8px;">
            <label>Webhook Token (opcional, header X-Webhook-Token)</label><br>
            <input type="text" name="webhook_token" value="<?php echo htmlspecialchars($settings['webhook_token'] ?? ''); ?>" size="60">
        </div>

        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <h2 style="margin-top: 20px;">Webhook</h2>
    <p>
        Endpoint: <code>/webhooks/asaas</code>
    </p>

    <h2 style="margin-top: 20px;">Eventos recentes</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Resource</th>
                <th>Recebido</th>
                <th>Processado</th>
                <th>Erro</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $e): ?>
                <tr>
                    <td><?php echo (int)$e['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)($e['event_type'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($e['resource_id'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($e['received_at'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($e['processed_at'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($e['processing_error'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
