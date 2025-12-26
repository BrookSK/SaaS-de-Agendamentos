

CREATE TABLE IF NOT EXISTS tenant_notification_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT UNSIGNED NOT NULL,
  event_name VARCHAR(120) NOT NULL,
  notify_client TINYINT(1) NOT NULL DEFAULT 1,
  notify_employee TINYINT(1) NOT NULL DEFAULT 1,
  notify_admin TINYINT(1) NOT NULL DEFAULT 1,
  channels_json JSON NULL, -- ex: {"email":true,"whatsapp":false}
  template_subject VARCHAR(255) NULL,
  template_body TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_tns_tenant_event (tenant_id, event_name),
  KEY idx_tns_tenant (tenant_id),
  CONSTRAINT fk_tns_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;
