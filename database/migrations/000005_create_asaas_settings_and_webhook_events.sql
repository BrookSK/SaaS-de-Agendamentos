

CREATE TABLE IF NOT EXISTS asaas_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  environment ENUM('sandbox','production') NOT NULL DEFAULT 'sandbox',
  api_key_sandbox VARCHAR(255) NULL,
  api_key_production VARCHAR(255) NULL,
  webhook_token VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

INSERT INTO asaas_settings (id, environment)
VALUES (1, 'sandbox')
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS asaas_webhook_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_type VARCHAR(80) NULL,
  resource_id VARCHAR(120) NULL,
  payload_json JSON NOT NULL,
  received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  processed_at DATETIME NULL,
  processing_error TEXT NULL,
  KEY idx_asaas_events_type (event_type),
  KEY idx_asaas_events_resource (resource_id)
) ENGINE=InnoDB;
