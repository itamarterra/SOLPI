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

-- ===========================================================
-- PHASE 2 - IDENTITY RESOLVER / MERGE CONFLICTS
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_identity_map` (
    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(30)  NOT NULL,
    `canonical_id` VARCHAR(64) NOT NULL,
    `entity_id`    BIGINT      NULL,
    `key_type`     VARCHAR(50) NOT NULL,
    `key_value`    VARCHAR(255) NOT NULL,
    `confidence`   DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    `source`       VARCHAR(100) NULL,
    `raw_hash`     VARCHAR(64) NULL,
    `metadata`     LONGTEXT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_entity_key` (`entity_type`, `key_type`, `key_value`),
    INDEX `idx_canonical` (`canonical_id`),
    INDEX `idx_entity_type` (`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_merge_conflicts` (
    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `correlation_id` VARCHAR(64) NULL,
    `entity_type`    VARCHAR(30) NOT NULL,
    `canonical_id`   VARCHAR(64) NULL,
    `field_path`     VARCHAR(150) NOT NULL,
    `current_value`  LONGTEXT NULL,
    `incoming_value` LONGTEXT NULL,
    `decision`       VARCHAR(30) NOT NULL DEFAULT 'KEPT_CURRENT',
    `reason`         VARCHAR(255) NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_correlation` (`correlation_id`),
    INDEX `idx_entity_type` (`entity_type`),
    INDEX `idx_canonical` (`canonical_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_companies` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid`       VARCHAR(100) NOT NULL,
    `name`       VARCHAR(255) NOT NULL,
    `trade_name` VARCHAR(255) NULL,
    `document`   VARCHAR(40)  NULL,
    `email`      VARCHAR(255) NULL,
    `phone`      VARCHAR(40)  NULL,
    `website`    VARCHAR(255) NULL,
    `address`    VARCHAR(255) NULL,
    `city`       VARCHAR(120) NULL,
    `state`      VARCHAR(120) NULL,
    `zip_code`   VARCHAR(20)  NULL,
    `active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `settings`   LONGTEXT NULL,
    `metadata`   LONGTEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_company_uuid` (`uuid`),
    INDEX `idx_company_document` (`document`),
    INDEX `idx_company_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_users` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid`       VARCHAR(100) NOT NULL,
    `name`       VARCHAR(255) NOT NULL,
    `email`      VARCHAR(255) NULL,
    `phone`      VARCHAR(40)  NULL,
    `department` VARCHAR(120) NULL,
    `position`   VARCHAR(120) NULL,
    `company_id` BIGINT NULL,
    `active`     TINYINT(1) NOT NULL DEFAULT 1,
    `settings`   LONGTEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_uuid` (`uuid`),
    INDEX `idx_user_email` (`email`),
    INDEX `idx_user_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_assets` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid`          VARCHAR(100) NOT NULL,
    `name`          VARCHAR(255) NOT NULL,
    `type`          VARCHAR(50) NOT NULL,
    `manufacturer`  VARCHAR(120) NULL,
    `model`         VARCHAR(120) NULL,
    `serial`        VARCHAR(120) NULL,
    `asset_tag`     VARCHAR(120) NULL,
    `company_id`    BIGINT NULL,
    `user_id`       BIGINT NULL,
    `location`      VARCHAR(255) NULL,
    `purchase_date` DATE NULL,
    `warranty_date` DATE NULL,
    `active`        TINYINT(1) NOT NULL DEFAULT 1,
    `metadata`      LONGTEXT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_asset_uuid` (`uuid`),
    INDEX `idx_asset_serial` (`serial`),
    INDEX `idx_asset_tag` (`asset_tag`),
    INDEX `idx_asset_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_review_queue` (
    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `correlation_id` VARCHAR(64) NULL,
    `entity_type`    VARCHAR(30) NOT NULL,
    `canonical_id`   VARCHAR(64) NULL,
    `confidence`     DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `payload`        LONGTEXT NOT NULL,
    `resolution`     LONGTEXT NULL,
    `conflicts`      LONGTEXT NULL,
    `status`         VARCHAR(30) NOT NULL DEFAULT 'PENDING',
    `decision_reason` VARCHAR(255) NULL,
    `reviewer_id`    INT UNSIGNED NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at`    TIMESTAMP NULL,
    INDEX `idx_review_status` (`status`),
    INDEX `idx_review_entity` (`entity_type`),
    INDEX `idx_review_confidence` (`confidence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_dead_letter` (
    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `job_id`      BIGINT NULL,
    `name`        VARCHAR(200) NOT NULL,
    `handler`     VARCHAR(500) NOT NULL,
    `payload`     LONGTEXT NULL,
    `error`       LONGTEXT NULL,
    `attempts`    TINYINT NOT NULL DEFAULT 0,
    `status`      VARCHAR(30) NOT NULL DEFAULT 'DEAD',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `replayed_at` TIMESTAMP NULL,
    INDEX `idx_dead_status` (`status`),
    INDEX `idx_dead_job` (`job_id`),
    INDEX `idx_dead_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_kg_nodes` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `canonical_id` VARCHAR(64) NOT NULL,
    `entity_type`  VARCHAR(30) NOT NULL,
    `entity_id`    BIGINT NULL,
    `label`        VARCHAR(255) NULL,
    `properties`   LONGTEXT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_kg_node` (`canonical_id`, `entity_type`),
    INDEX `idx_kg_entity_type` (`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_kg_edges` (
    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_canonical_id` VARCHAR(64) NOT NULL,
    `target_canonical_id` VARCHAR(64) NOT NULL,
    `relation`     VARCHAR(80) NOT NULL,
    `weight`       DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    `properties`   LONGTEXT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_kg_edge` (`source_canonical_id`, `target_canonical_id`, `relation`),
    INDEX `idx_kg_source` (`source_canonical_id`),
    INDEX `idx_kg_target` (`target_canonical_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_data_quality_reports` (
    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `scope`          VARCHAR(80) NOT NULL DEFAULT 'integration_engine',
    `records_total`  BIGINT NOT NULL DEFAULT 0,
    `records_valid`  BIGINT NOT NULL DEFAULT 0,
    `records_review` BIGINT NOT NULL DEFAULT 0,
    `records_dead`   BIGINT NOT NULL DEFAULT 0,
    `quality_score`  DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `details`        LONGTEXT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_quality_scope` (`scope`),
    INDEX `idx_quality_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- PHASE 6 - SOURCE CHECKPOINTS
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_source_checkpoints` (
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source`     VARCHAR(100) NOT NULL,
    `adapter`    VARCHAR(60)  NOT NULL,
    `name`       VARCHAR(120) NOT NULL DEFAULT 'default',
    `last_value` LONGTEXT     NOT NULL,
    `metadata`   LONGTEXT     NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_source_adapter_name` (`source`, `adapter`, `name`),
    INDEX `idx_source_adapter` (`source`, `adapter`),
    INDEX `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;