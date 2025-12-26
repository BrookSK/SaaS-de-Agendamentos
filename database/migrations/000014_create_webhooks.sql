USE saas_agendamentos;

CREATE TABLE IF NOT EXISTS system_webhooks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_name VARCHAR(120) NOT NULL,
  url VARCHAR(500) NOT NULL,
  secret VARCHAR(255) NULL,
  environment ENUM('test','production') NOT NULL DEFAULT 'test',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY idx_sw_event (event_name),
  KEY idx_sw_env_active (environment, active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS system_webhook_deliveries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  webhook_id INT UNSIGNED NOT NULL,
  event_name VARCHAR(120) NOT NULL,
  payload_json JSON NOT NULL,
  status ENUM('pending','success','failed') NOT NULL DEFAULT 'pending',
  attempt_count INT UNSIGNED NOT NULL DEFAULT 0,
  last_attempt_at DATETIME NULL,
  next_attempt_at DATETIME NULL,
  response_code INT NULL,
  response_body TEXT NULL,
  error TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY idx_swd_webhook (webhook_id),
  KEY idx_swd_status_next (status, next_attempt_at),
  KEY idx_swd_event (event_name),
  CONSTRAINT fk_swd_webhook FOREIGN KEY (webhook_id) REFERENCES system_webhooks(id) ON DELETE CASCADE
) ENGINE=InnoDB;
