

ALTER TABLE tenants
  ADD COLUMN email VARCHAR(190) NULL,
  ADD COLUMN phone VARCHAR(30) NULL,
  ADD COLUMN cpf_cnpj VARCHAR(20) NULL;

CREATE INDEX idx_tenants_cpf_cnpj ON tenants (cpf_cnpj);
