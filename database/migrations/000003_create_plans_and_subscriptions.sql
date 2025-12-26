USE saas_agendamentos;

CREATE TABLE IF NOT EXISTS plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT NULL,
  price_cents INT UNSIGNED NOT NULL,
  billing_cycle ENUM('monthly','semiannual','annual') NOT NULL DEFAULT 'monthly',
  active TINYINT(1) NOT NULL DEFAULT 1,
  limits_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tenant_subscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  status ENUM('active','canceled','past_due','blocked','trial') NOT NULL DEFAULT 'trial',
  started_at DATETIME NULL,
  renews_at DATETIME NULL,
  canceled_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY idx_subscriptions_tenant (tenant_id),
  KEY idx_subscriptions_plan (plan_id),
  CONSTRAINT fk_subscriptions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tenant_subscription_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT UNSIGNED NOT NULL,
  provider ENUM('asaas','manual') NOT NULL DEFAULT 'manual',
  provider_payment_id VARCHAR(100) NULL,
  amount_cents INT UNSIGNED NOT NULL,
  status ENUM('pending','paid','failed','canceled') NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_payments_subscription (subscription_id),
  CONSTRAINT fk_payments_subscription FOREIGN KEY (subscription_id) REFERENCES tenant_subscriptions(id) ON DELETE CASCADE
) ENGINE=InnoDB;
