USE saas_agendamentos;

CREATE TABLE IF NOT EXISTS financial_transactions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  type ENUM('in','out') NOT NULL,
  category VARCHAR(100) NULL,
  description VARCHAR(255) NULL,
  amount_cents INT UNSIGNED NOT NULL,
  occurred_on DATE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY idx_ft_tenant (tenant_id),
  KEY idx_ft_tenant_date (tenant_id, occurred_on),
  CONSTRAINT fk_ft_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;
