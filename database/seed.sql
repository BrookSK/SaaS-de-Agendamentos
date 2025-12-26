

-- SUPER ADMIN (tenant_id = NULL)
-- senha: admin123 (troque depois)
INSERT INTO users (tenant_id, name, email, password_hash, role, status)
VALUES (NULL, 'Super Admin', 'admin@admin.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgs5eZx2drXr/7Lr1gChX2NDSSha', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE email=email;

-- TENANT EXEMPLO
INSERT INTO tenants (name, slug, status)
VALUES ('Empresa Demo', 'demo', 'active')
ON DUPLICATE KEY UPDATE slug=slug;

SET @tenant_id = (SELECT id FROM tenants WHERE slug = 'demo' LIMIT 1);

-- ADMIN DO TENANT
-- senha: admin123
INSERT INTO users (tenant_id, name, email, password_hash, role, status)
VALUES (@tenant_id, 'Admin Demo', 'admin@demo.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgs5eZx2drXr/7Lr1gChX2NDSSha', 'tenant_admin', 'active')
ON DUPLICATE KEY UPDATE email=email;
