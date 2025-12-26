<?php
/** @var array<int, array<string,mixed>> $webhooks */
/** @var array<int, array<string,mixed>> $deliveries */
/** @var string|null $message */
/** @var string|null $error */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Webhooks</title>
</head>
<body>
    <h1>Webhooks (Global)</h1>
    <p><a href="/super/dashboard">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color:#b00;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="/super/webhooks/process">
        <button type="submit">Processar fila (retries)</button>
    </form>

    <h2>Novo webhook</h2>
    <form method="post" action="/super/webhooks">
        <div>
            <label>Evento (ex: appointment.created)</label><br>
            <input type="text" name="event_name" required>
        </div>
        <div style="margin-top: 8px;">
            <label>URL</label><br>
            <input type="text" name="url" size="80" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Secret (HMAC opcional)</label><br>
            <input type="text" name="secret" size="60">
        </div>
        <div style="margin-top: 8px;">
            <label>Ambiente</label><br>
            <select name="environment">
                <option value="test">Teste</option>
                <option value="production">Produção</option>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>Ativo</label><br>
            <select name="active">
                <option value="1">Sim</option>
                <option value="0">Não</option>
            </select>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <h2>Webhooks cadastrados</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Evento</th>
                <th>URL</th>
                <th>Env</th>
                <th>Ativo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($webhooks as $w): ?>
                <tr>
                    <td><?php echo (int)$w['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$w['event_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$w['url']); ?></td>
                    <td><?php echo htmlspecialchars((string)$w['environment']); ?></td>
                    <td><?php echo (int)$w['active'] === 1 ? 'Sim' : 'Não'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Logs de envio</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Evento</th>
                <th>Status</th>
                <th>Tentativas</th>
                <th>Última tentativa</th>
                <th>Próxima tentativa</th>
                <th>HTTP</th>
                <th>Erro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deliveries as $d): ?>
                <tr>
                    <td><?php echo (int)$d['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$d['event_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$d['status']); ?></td>
                    <td><?php echo (int)$d['attempt_count']; ?></td>
                    <td><?php echo htmlspecialchars((string)($d['last_attempt_at'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($d['next_attempt_at'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($d['response_code'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars((string)($d['error'] ?? '')); ?></td>
                    <td>
                        <form method="post" action="/super/webhooks/resend" style="display:inline;">
                            <input type="hidden" name="delivery_id" value="<?php echo (int)$d['id']; ?>">
                            <button type="submit">Reenviar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Headers enviados</h2>
    <p>
        <code>Content-Type: application/json</code><br>
        <code>X-Event: &lt;event_name&gt;</code><br>
        <code>X-Timestamp: &lt;unix timestamp&gt;</code><br>
        <code>X-Signature: &lt;hmac sha256 do body&gt;</code> (se secret configurado)
    </p>
</body>
</html>
