<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<int, string> $events */
/** @var array<string, array<string,mixed>> $map */
/** @var string|null $message */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notificações</title>
</head>
<body>
    <h1>Notificações (por evento)</h1>
    <p><a href="<?php echo htmlspecialchars($tenant->urlPrefix() . '/dashboard'); ?>">Voltar</a></p>

    <?php if (!empty($message)): ?>
        <p style="color:#070;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($tenant->urlPrefix() . '/settings/notifications'); ?>">
        <p>
            Você pode escolher quem recebe (cliente/funcionário/admin) e quais canais ficam ativos.
            Por enquanto, o canal <strong>Webhook</strong> é o principal (integrações externas).
        </p>

        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Cliente</th>
                    <th>Funcionário</th>
                    <th>Admin</th>
                    <th>Email</th>
                    <th>Webhook</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <?php $r = $map[$event] ?? null; ?>
                    <?php
                        $notifyClient = (int)($r['notify_client'] ?? 0);
                        $notifyEmployee = (int)($r['notify_employee'] ?? 0);
                        $notifyAdmin = (int)($r['notify_admin'] ?? 1);

                        $channels = [];
                        if (isset($r['channels_json']) && $r['channels_json'] !== null) {
                            $decoded = json_decode((string)$r['channels_json'], true);
                            if (is_array($decoded)) {
                                $channels = $decoded;
                            }
                        }

                        $channelEmail = (bool)($channels['email'] ?? false);
                        $channelWebhook = (bool)($channels['webhook'] ?? true);

                        $subject = (string)($r['template_subject'] ?? '');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event); ?></td>
                        <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_notify_client'); ?>" value="1" <?php echo $notifyClient === 1 ? 'checked' : ''; ?>></td>
                        <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_notify_employee'); ?>" value="1" <?php echo $notifyEmployee === 1 ? 'checked' : ''; ?>></td>
                        <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_notify_admin'); ?>" value="1" <?php echo $notifyAdmin === 1 ? 'checked' : ''; ?>></td>
                        <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_channel_email'); ?>" value="1" <?php echo $channelEmail ? 'checked' : ''; ?>></td>
                        <td><input type="checkbox" name="<?php echo htmlspecialchars($event . '_channel_webhook'); ?>" value="1" <?php echo $channelWebhook ? 'checked' : ''; ?>></td>
                        <td><input type="text" name="<?php echo htmlspecialchars($event . '_subject'); ?>" value="<?php echo htmlspecialchars($subject); ?>" size="30"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>
</body>
</html>
