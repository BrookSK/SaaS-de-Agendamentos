USE saas_agendamentos;

CREATE TABLE IF NOT EXISTS financial_titles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  type ENUM('payable','receivable') NOT NULL,
  status ENUM('open','paid','canceled') NOT NULL DEFAULT 'open',
  category VARCHAR(100) NULL,
  description VARCHAR(255) NULL,
  amount_cents INT UNSIGNED NOT NULL,
  due_on DATE NOT NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY idx_ftt_tenant (tenant_id),
  KEY idx_ftt_tenant_due (tenant_id, due_on),
  KEY idx_ftt_tenant_status (tenant_id, status),
  CONSTRAINT fk_ftt_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;
