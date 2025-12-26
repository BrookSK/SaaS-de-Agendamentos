

-- Super Admin padrão
-- Login: admin@admin.com
-- Senha: (hash fornecido)
INSERT INTO users (tenant_id, name, email, password_hash, role, status)
SELECT NULL, 'Super Admin', 'admin@admin.com', '$2y$10$3YAHki.1HX7vSHh3OaO1JuV1KUdrNfmIkseijCKhn05yCQPP/shIu', 'super_admin', 'active'
WHERE NOT EXISTS (
  SELECT 1 FROM users WHERE tenant_id IS NULL AND email = 'admin@admin.com' LIMIT 1
);
