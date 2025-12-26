CREATE DATABASE IF NOT EXISTS saas_agendamentos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE saas_agendamentos;

CREATE TABLE IF NOT EXISTS migrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_migrations_filename (filename)
) ENGINE=InnoDB;
