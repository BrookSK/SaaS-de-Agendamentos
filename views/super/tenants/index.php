<?php
/** @var array<int, \App\Models\Tenant> $tenants */

$created = (string)($_GET['created'] ?? '');
$loginUrl = (string)($_GET['login_url'] ?? '');
$adminEmail = (string)($_GET['admin_email'] ?? '');
$emailStatus = (string)($_GET['email_status'] ?? '');
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Empresas</title>
</head>
<body>
    <h1>Empresas (Tenants)</h1>

    <?php if ($created === '1' && $loginUrl !== ''): ?>
        <div class="notice success" id="tenant-created-notice">
            <div><strong>Empresa criada com sucesso.</strong></div>
            <div style="margin-top: 6px;">Link de login: <a href="<?php echo htmlspecialchars($loginUrl); ?>"><?php echo htmlspecialchars($loginUrl); ?></a></div>
            <?php if ($adminEmail !== ''): ?>
                <div style="margin-top: 6px;">E-mail do dono: <strong><?php echo htmlspecialchars($adminEmail); ?></strong></div>
            <?php endif; ?>
            <?php if ($emailStatus !== ''): ?>
                <div style="margin-top: 6px;">
                    Status do e-mail: <strong><?php echo htmlspecialchars($emailStatus); ?></strong>
                </div>
            <?php endif; ?>
            <div style="margin-top: 10px;" class="actions">
                <button type="button" class="btn" id="copy-login-url-btn">Copiar link</button>
            </div>
            <div style="margin-top: 8px;" class="small" id="copy-login-url-status"></div>
        </div>
        <script>
            (function () {
                var loginUrl = <?php echo json_encode($loginUrl, JSON_UNESCAPED_SLASHES); ?>;
                var btn = document.getElementById('copy-login-url-btn');
                var status = document.getElementById('copy-login-url-status');

                function setStatus(text) {
                    if (status) status.textContent = text;
                }

                async function copy() {
                    try {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(loginUrl);
                            setStatus('Link copiado para a área de transferência.');
                            return;
                        }
                    } catch (e) {}

                    try {
                        var ta = document.createElement('textarea');
                        ta.value = loginUrl;
                        ta.setAttribute('readonly', 'readonly');
                        ta.style.position = 'fixed';
                        ta.style.left = '-9999px';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                        setStatus('Link copiado para a área de transferência.');
                    } catch (e2) {
                        setStatus('Não foi possível copiar automaticamente. Copie manualmente pelo link acima.');
                    }
                }

                if (btn) {
                    btn.addEventListener('click', function () { copy(); });
                }

                // tenta copiar automaticamente (alguns navegadores podem bloquear sem interação)
                copy();
            })();
        </script>
    <?php endif; ?>

    <p><a href="/super/dashboard">Voltar</a></p>

    <h2>Criar empresa</h2>
    <form method="post" action="/super/tenants">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required id="tenant_name">
        </div>
        <div style="margin-top: 8px;">
            <label>Slug (ex: minha-empresa)</label><br>
            <input type="text" name="slug" pattern="[a-z0-9-]+" id="tenant_slug" placeholder="gerado automaticamente">
        </div>
        <div style="margin-top: 8px;">
            <label>Status</label><br>
            <select name="status">
                <option value="active">Ativo</option>
                <option value="blocked">Bloqueado</option>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>E-mail (ASAAS customer)</label><br>
            <input type="email" name="email">
        </div>
        <div style="margin-top: 8px;">
            <label>Telefone (ASAAS customer)</label><br>
            <input type="text" name="phone">
        </div>
        <div style="margin-top: 8px;">
            <label>CPF/CNPJ (ASAAS customer)</label><br>
            <input type="text" name="cpf_cnpj">
        </div>
        <div style="margin-top: 18px;">
            <strong>Administrador da empresa</strong>
        </div>
        <div style="margin-top: 8px;">
            <label>E-mail do admin (login do dono)</label><br>
            <input type="email" name="admin_email" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Senha do admin</label><br>
            <input type="password" name="admin_password" required>
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Salvar</button>
        </div>
    </form>

    <script>
        (function () {
            var nameInput = document.getElementById('tenant_name');
            var slugInput = document.getElementById('tenant_slug');
            if (!nameInput || !slugInput) return;

            function slugify(s) {
                return String(s || '')
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }

            nameInput.addEventListener('input', function () {
                if (slugInput.value && slugInput.dataset.touched === '1') return;
                slugInput.value = slugify(nameInput.value);
            });

            slugInput.addEventListener('input', function () {
                slugInput.dataset.touched = '1';
                slugInput.value = slugify(slugInput.value);
            });
        })();
    </script>

    <h2>Lista</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Slug</th>
                <th>Status</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>CPF/CNPJ</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tenants as $t): ?>
                <tr>
                    <td><?php echo (int)$t->id; ?></td>
                    <td><?php echo htmlspecialchars($t->name); ?></td>
                    <td><?php echo htmlspecialchars($t->slug); ?></td>
                    <td><?php echo htmlspecialchars($t->status); ?></td>
                    <td><?php echo htmlspecialchars($t->email ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($t->phone ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($t->cpfCnpj ?? ''); ?></td>
                    <td>
                        <a href="/super/tenants/<?php echo (int)$t->id; ?>/edit">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
