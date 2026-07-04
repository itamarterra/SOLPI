-- Migration: add token management fields
ALTER TABLE `glpi_plugins_solpi_installations`
  ADD COLUMN `token_revoked` TINYINT(1) DEFAULT 0,
  ADD COLUMN `token_revoked_at` DATETIME DEFAULT NULL,
  ADD COLUMN `token_last_rotated` DATETIME DEFAULT NULL;
