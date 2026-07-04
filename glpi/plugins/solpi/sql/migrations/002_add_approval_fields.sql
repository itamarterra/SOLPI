-- Migration: Add approval fields to solpi_installations
ALTER TABLE `glpi_plugins_solpi_installations`
  ADD COLUMN `approved` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `approved_by` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN `approved_at` DATETIME DEFAULT NULL;
