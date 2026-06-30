/*
|--------------------------------------------------------------------------
| SOLPI Professional
|--------------------------------------------------------------------------
| Update Database
|--------------------------------------------------------------------------
| Version: 2.0.0-alpha
|--------------------------------------------------------------------------
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

START TRANSACTION;

-- ===========================================================
-- CONFIG
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_config
    ADD COLUMN IF NOT EXISTS enabled TINYINT(1) DEFAULT 1;

-- ===========================================================
-- ALERTS
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_alerts
    ADD COLUMN IF NOT EXISTS acknowledged TINYINT(1) DEFAULT 0;

ALTER TABLE glpi_plugin_solpi_alerts
    ADD COLUMN IF NOT EXISTS resolved_at TIMESTAMP NULL;

-- ===========================================================
-- TICKETS
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_tickets
    ADD COLUMN IF NOT EXISTS sla_time INT DEFAULT NULL;

ALTER TABLE glpi_plugin_solpi_tickets
    ADD COLUMN IF NOT EXISTS resolution_time INT DEFAULT NULL;

-- ===========================================================
-- WHATSAPP
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_whatsapp
    ADD COLUMN IF NOT EXISTS message_id VARCHAR(255);

ALTER TABLE glpi_plugin_solpi_whatsapp
    ADD COLUMN IF NOT EXISTS delivered TINYINT(1) DEFAULT 0;

ALTER TABLE glpi_plugin_solpi_whatsapp
    ADD COLUMN IF NOT EXISTS read_at TIMESTAMP NULL;

-- ===========================================================
-- AI
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_ai
    ADD COLUMN IF NOT EXISTS temperature DECIMAL(4,2) DEFAULT 0.70;

ALTER TABLE glpi_plugin_solpi_ai
    ADD COLUMN IF NOT EXISTS finish_reason VARCHAR(50);

-- ===========================================================
-- KNOWLEDGE
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_knowledge
    ADD COLUMN IF NOT EXISTS views INT DEFAULT 0;

ALTER TABLE glpi_plugin_solpi_knowledge
    ADD COLUMN IF NOT EXISTS rating DECIMAL(3,2) DEFAULT 0;

-- ===========================================================
-- JOBS
-- ===========================================================

ALTER TABLE glpi_plugin_solpi_jobs
    ADD COLUMN IF NOT EXISTS worker VARCHAR(100);

ALTER TABLE glpi_plugin_solpi_jobs
    ADD COLUMN IF NOT EXISTS execution_log LONGTEXT;

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;