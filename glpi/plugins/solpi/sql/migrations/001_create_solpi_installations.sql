-- Migration: Create solpi_installations table
CREATE TABLE IF NOT EXISTS `glpi_plugins_solpi_installations` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `site_name` VARCHAR(255) NOT NULL,
  `site_url` VARCHAR(1024) DEFAULT NULL,
  `glpi_version` VARCHAR(64) DEFAULT NULL,
  `solpi_version` VARCHAR(64) DEFAULT NULL,
  `ip_address` VARCHAR(64) DEFAULT NULL,
  `capabilities` JSON DEFAULT NULL,
  `inventory` JSON DEFAULT NULL,
  `status` VARCHAR(32) DEFAULT 'offline',
  `last_seen` DATETIME DEFAULT NULL,
  `auth_token` VARCHAR(256) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
