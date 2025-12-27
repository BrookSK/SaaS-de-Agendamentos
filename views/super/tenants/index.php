<?php
/** @var array<int, \App\Models\Tenant> $tenants */
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

    <p><a href="/super/dashboard">Voltar</a></p>

    <h2>Criar empresa</h2>
    <form method="post" action="/super/tenants">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required>
        </div>
        <div style="margin-top: 8px;">
            <label>Slug (ex: minha-empresa)</label><br>
            <input type="text" name="slug" pattern="[a-z0-9-]+" required>
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
