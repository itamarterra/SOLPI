/*
|--------------------------------------------------------------------------
| SOLPI Professional
|--------------------------------------------------------------------------
| Smart Operational Learning Platform for IT
|--------------------------------------------------------------------------
| Version: 2.0.0
|--------------------------------------------------------------------------
| Todas as tabelas usam o prefixo glpi_plugin_solpi_
| CREATE TABLE IF NOT EXISTS garante idempotência na instalação
|--------------------------------------------------------------------------
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ===========================================================
-- CONFIGURAÇÃO GLOBAL DO PLUGIN
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_config` (

    `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `zabbix_url`       VARCHAR(255) NULL,
    `zabbix_token`     VARCHAR(255) NULL,

    `evolution_url`    VARCHAR(255) NULL,
    `evolution_token`  VARCHAR(255) NULL,

    `ai_provider`      VARCHAR(50)  NOT NULL DEFAULT 'openai',
    `ai_model`         VARCHAR(100) NOT NULL DEFAULT 'gpt-4o',
    `ai_api_key`       TEXT NULL,

    `timezone`         VARCHAR(100) NOT NULL DEFAULT 'America/Sao_Paulo',
    `language`         VARCHAR(10)  NOT NULL DEFAULT 'pt_BR',

    `enabled`          TINYINT(1)   NOT NULL DEFAULT 1,

    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                       ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- CONFIGURAÇÕES POR MÓDULO
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_settings` (

    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `module`     VARCHAR(100) NOT NULL,
    `key`        VARCHAR(100) NOT NULL,
    `value`      LONGTEXT     NULL,
    `type`       VARCHAR(30)  NOT NULL DEFAULT 'string',

    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_module_key` (`module`, `key`),
    INDEX `idx_module` (`module`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- ALERTAS DO ZABBIX
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_alerts` (

    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `eventid`      BIGINT          NULL,
    `host`         VARCHAR(255)    NOT NULL,
    `trigger_name` VARCHAR(500)    NOT NULL,
    `severity`     VARCHAR(30)     NOT NULL,
    `status`       VARCHAR(30)     NOT NULL DEFAULT 'OPEN',
    `ticket_id`    INT             NULL,

    `raw_data`     LONGTEXT        NULL,

    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_host`     (`host`),
    INDEX `idx_status`   (`status`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_eventid`  (`eventid`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- SINCRONIA DE TICKETS (SOLPI <-> GLPI)
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_tickets` (

    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `glpi_ticket_id` INT    NOT NULL,
    `alert_id`       BIGINT NULL,
    `assigned_to`    INT    NULL,
    `priority`       INT    NOT NULL DEFAULT 3,
    `status`         VARCHAR(30) NOT NULL DEFAULT 'OPEN',
    `rating`         TINYINT UNSIGNED NULL,

    `opened_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `closed_at`  TIMESTAMP NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_glpi_ticket` (`glpi_ticket_id`),
    INDEX `idx_alert`       (`alert_id`),
    INDEX `idx_status`      (`status`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- MENSAGENS WHATSAPP
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_whatsapp` (

    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `ticket_id`  BIGINT       NULL,
    `phone`      VARCHAR(30)  NOT NULL,
    `direction`  VARCHAR(20)  NOT NULL,
    `message`    LONGTEXT     NOT NULL,
    `status`     VARCHAR(30)  NOT NULL DEFAULT 'PENDING',
    `response`   LONGTEXT     NULL,

    `sent_at`    TIMESTAMP    NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_ticket` (`ticket_id`),
    INDEX `idx_phone`  (`phone`),
    INDEX `idx_status` (`status`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- INTERAÇÕES DE IA
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_ai` (

    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `ticket_id`      BIGINT          NULL,
    `provider`       VARCHAR(50)     NOT NULL,
    `model`          VARCHAR(100)    NOT NULL,
    `prompt`         LONGTEXT        NOT NULL,
    `response`       LONGTEXT        NULL,
    `tokens`         INT UNSIGNED    NOT NULL DEFAULT 0,
    `execution_time` DECIMAL(10, 3)  NOT NULL DEFAULT 0.000,

    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_ticket`   (`ticket_id`),
    INDEX `idx_provider` (`provider`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- MEMÓRIA DE CONVERSAÇÃO (IA)
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_conversations` (

    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `session_id` VARCHAR(100) NOT NULL,
    `ticket_id`  BIGINT       NULL,
    `phone`      VARCHAR(30)  NULL,
    `role`       VARCHAR(20)  NOT NULL,
    `content`    LONGTEXT     NOT NULL,

    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_session`  (`session_id`),
    INDEX `idx_ticket`   (`ticket_id`),
    INDEX `idx_phone`    (`phone`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- EMBEDDINGS VETORIAIS (IA / RAG)
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_embeddings` (

    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `source_type` VARCHAR(50)  NOT NULL,
    `source_id`   BIGINT       NOT NULL,
    `chunk_index` INT UNSIGNED NOT NULL DEFAULT 0,
    `content`     LONGTEXT     NOT NULL,
    `embedding`   LONGTEXT     NULL,
    `model`       VARCHAR(100) NOT NULL DEFAULT 'text-embedding-ada-002',

    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_source_chunk` (`source_type`, `source_id`, `chunk_index`),
    INDEX `idx_source` (`source_type`, `source_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- BASE DE CONHECIMENTO — ARTIGOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_knowledge` (

    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `title`       VARCHAR(500)  NOT NULL,
    `content`     LONGTEXT      NOT NULL,
    `summary`     TEXT          NULL,
    `source_type` VARCHAR(50)   NULL,
    `source_file` VARCHAR(500)  NULL,
    `tags`        TEXT          NULL,

    `status`      VARCHAR(30)   NOT NULL DEFAULT 'ACTIVE',
    `views`       INT UNSIGNED  NOT NULL DEFAULT 0,

    `created_by`  INT           NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_status`      (`status`),
    INDEX `idx_source_type` (`source_type`),
    FULLTEXT KEY `ft_title_content` (`title`, `content`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- BASE DE CONHECIMENTO — ENTIDADES EXTRAÍDAS
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_knowledge_entities` (

    `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `knowledge_id`     BIGINT       NOT NULL,
    `entity_type`      VARCHAR(50)  NOT NULL,
    `entity_value`     VARCHAR(500) NOT NULL,
    `normalized_value` VARCHAR(500) NULL,
    `confidence`       DECIMAL(5,4) NOT NULL DEFAULT 1.0000,

    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_knowledge`    (`knowledge_id`),
    INDEX `idx_entity_type`  (`entity_type`),
    INDEX `idx_entity_value` (`entity_value`(100))

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- BASE DE CONHECIMENTO — GRAFO DE RELACIONAMENTOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_knowledge_relationships` (

    `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `source_id`   BIGINT       NOT NULL,
    `target_id`   BIGINT       NOT NULL,
    `relation`    VARCHAR(100) NOT NULL,
    `weight`      DECIMAL(5,4) NOT NULL DEFAULT 1.0000,

    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_relationship` (`source_id`, `target_id`, `relation`),
    INDEX `idx_source` (`source_id`),
    INDEX `idx_target` (`target_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- CACHE DO DASHBOARD
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_dashboard` (

    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `metric`     VARCHAR(100) NOT NULL,
    `value`      LONGTEXT     NOT NULL,
    `expires_at` TIMESTAMP    NOT NULL,

    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_metric` (`metric`),
    INDEX `idx_expires` (`expires_at`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- JOBS DO SCHEDULER
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_jobs` (

    `id`           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `name`         VARCHAR(200) NOT NULL,
    `handler`      VARCHAR(500) NOT NULL,
    `payload`      LONGTEXT     NULL,
    `status`       VARCHAR(30)  NOT NULL DEFAULT 'PENDING',
    `attempts`     TINYINT      NOT NULL DEFAULT 0,
    `max_attempts` TINYINT      NOT NULL DEFAULT 3,
    `error`        TEXT         NULL,

    `scheduled_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `started_at`   TIMESTAMP    NULL,
    `finished_at`  TIMESTAMP    NULL,
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_status`        (`status`),
    INDEX `idx_scheduled_at`  (`scheduled_at`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- LOG DE WEBHOOKS RECEBIDOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_webhooks` (

    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `source`     VARCHAR(50)  NOT NULL,
    `event`      VARCHAR(100) NOT NULL,
    `payload`    LONGTEXT     NOT NULL,
    `status`     VARCHAR(30)  NOT NULL DEFAULT 'RECEIVED',
    `ip_address` VARCHAR(45)  NULL,
    `error`      TEXT         NULL,

    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_source` (`source`),
    INDEX `idx_status` (`status`),
    INDEX `idx_event`  (`event`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- FILA DE NOTIFICAÇÕES
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_notifications` (

    `id`        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `channel`   VARCHAR(30)  NOT NULL,
    `recipient` VARCHAR(255) NOT NULL,
    `subject`   VARCHAR(500) NULL,
    `body`      LONGTEXT     NOT NULL,
    `status`    VARCHAR(30)  NOT NULL DEFAULT 'PENDING',
    `ticket_id` BIGINT       NULL,
    `error`     TEXT         NULL,

    `sent_at`   TIMESTAMP    NULL,
    `created_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_channel`  (`channel`),
    INDEX `idx_status`   (`status`),
    INDEX `idx_ticket`   (`ticket_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================================
-- LOGS DE AUDITORIA
-- ===========================================================

CREATE TABLE IF NOT EXISTS `glpi_plugin_solpi_logs` (

    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `module`     VARCHAR(100) NOT NULL,
    `level`      VARCHAR(20)  NOT NULL,
    `message`    LONGTEXT     NOT NULL,
    `context`    LONGTEXT     NULL,
    `ip_address` VARCHAR(45)  NULL,
    `user_id`    INT UNSIGNED NULL,

    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_module`     (`module`),
    INDEX `idx_level`      (`level`),
    INDEX `idx_user`       (`user_id`),
    INDEX `idx_created_at` (`created_at`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
