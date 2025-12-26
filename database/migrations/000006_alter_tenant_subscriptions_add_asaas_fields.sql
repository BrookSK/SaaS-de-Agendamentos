

ALTER TABLE tenant_subscriptions
  ADD COLUMN asaas_customer_id VARCHAR(100) NULL,
  ADD COLUMN asaas_subscription_id VARCHAR(100) NULL,
  ADD COLUMN asaas_last_event_at DATETIME NULL,
  ADD COLUMN asaas_last_status VARCHAR(50) NULL,
  ADD KEY idx_ts_asaas_subscription (asaas_subscription_id);
