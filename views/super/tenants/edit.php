<?php
/** @var \App\Models\Tenant $tenant */
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin - Editar empresa</title>
</head>
<body>
    <h1>Editar empresa</h1>

    <p><a href="/super/tenants">Voltar</a></p>

    <form method="post" action="/super/tenants/<?php echo (int)$tenant->id; ?>">
        <div>
            <label>Nome</label><br>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($tenant->name); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Slug (ex: minha-empresa)</label><br>
            <input type="text" name="slug" pattern="[a-z0-9-]+" required value="<?php echo htmlspecialchars($tenant->slug); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Status</label><br>
            <select name="status">
                <option value="active" <?php echo $tenant->status === 'active' ? 'selected' : ''; ?>>Ativo</option>
                <option value="blocked" <?php echo $tenant->status === 'blocked' ? 'selected' : ''; ?>>Bloqueado</option>
            </select>
        </div>
        <div style="margin-top: 8px;">
            <label>E-mail (ASAAS customer)</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($tenant->email ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>Telefone (ASAAS customer)</label><br>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($tenant->phone ?? ''); ?>">
        </div>
        <div style="margin-top: 8px;">
            <label>CPF/CNPJ (ASAAS customer)</label><br>
            <input type="text" name="cpf_cnpj" value="<?php echo htmlspecialchars($tenant->cpfCnpj ?? ''); ?>">
        </div>
        <div style="margin-top: 12px;">
            <button type="submit">Salvar alterações</button>
        </div>
    </form>
</body>
</html>
