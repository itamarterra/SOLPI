/*
|--------------------------------------------------------------------------
| SOLPI Professional
|--------------------------------------------------------------------------
| Smart Operations Link for GLPI
|--------------------------------------------------------------------------
| Version: 2.0.0-alpha
|--------------------------------------------------------------------------
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ===========================================================
-- CONFIGURAÇÕES
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_config (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    zabbix_url VARCHAR(255) NULL,
    zabbix_token VARCHAR(255) NULL,

    evolution_url VARCHAR(255) NULL,
    evolution_token VARCHAR(255) NULL,

    ai_provider VARCHAR(50) DEFAULT 'openai',
    ai_model VARCHAR(100) DEFAULT 'gpt-5.5',

    ai_api_key TEXT NULL,

    timezone VARCHAR(100) DEFAULT 'America/Sao_Paulo',

    language VARCHAR(10) DEFAULT 'pt_BR',

    enabled TINYINT(1) DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP

);

-- ===========================================================
-- ALERTAS
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_alerts (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    eventid BIGINT NULL,

    host VARCHAR(255) NOT NULL,

    trigger_name VARCHAR(255) NOT NULL,

    severity VARCHAR(30) NOT NULL,

    status VARCHAR(30) DEFAULT 'OPEN',

    ticket_id INT NULL,

    raw_data LONGTEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(host),
    INDEX(status),
    INDEX(severity)

);

-- ===========================================================
-- TICKETS
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_tickets (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    glpi_ticket_id INT NOT NULL,

    alert_id BIGINT NULL,

    assigned_to INT NULL,

    priority INT DEFAULT 3,

    status VARCHAR(30) DEFAULT 'OPEN',

    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    closed_at TIMESTAMP NULL,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    INDEX(glpi_ticket_id),
    INDEX(status)

);

-- ===========================================================
-- WHATSAPP
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_whatsapp (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT NULL,

    phone VARCHAR(30),

    direction VARCHAR(20),

    message LONGTEXT,

    status VARCHAR(30),

    response LONGTEXT,

    sent_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(ticket_id),
    INDEX(phone)

);

-- ===========================================================
-- IA
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ticket_id BIGINT NULL,

    provider VARCHAR(50),

    model VARCHAR(100),

    prompt LONGTEXT,

    response LONGTEXT,

    tokens INT DEFAULT 0,

    execution_time DECIMAL(10,3) DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(ticket_id)

);
-- ===========================================================
-- LOGS
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_logs (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    module VARCHAR(100) NOT NULL,

    level VARCHAR(20) NOT NULL,

    message LONGTEXT NOT NULL,

    context LONGTEXT NULL,

    ip_address VARCHAR(45) NULL,

    user_id INT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(module),
    INDEX(level),
    INDEX(user_id),
    INDEX(created_at)

);

-- ===========================================================
-- DASHBOARD CACHE
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_dashboard (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    metric VARCHAR(100) NOT NULL,

    metric_value VARCHAR(255) NOT NULL,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE(metric)

);

-- ===========================================================
-- KNOWLEDGE BASE
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_knowledge (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,

    category VARCHAR(100) NULL,

    keywords TEXT NULL,

    content LONGTEXT NOT NULL,

    embedding LONGTEXT NULL,

    created_by INT NULL,

    active TINYINT(1) DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    INDEX(category),
    INDEX(active),
    INDEX(created_by)

);

-- ===========================================================
-- WEBHOOKS
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_webhooks (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    source VARCHAR(100) NOT NULL,

    endpoint VARCHAR(255) NOT NULL,

    secret VARCHAR(255) NULL,

    status TINYINT(1) DEFAULT 1,

    last_execution TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(source),
    INDEX(status)

);

-- ===========================================================
-- JOB QUEUE
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_jobs (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    job_type VARCHAR(100) NOT NULL,

    payload LONGTEXT,

    status VARCHAR(30) DEFAULT 'PENDING',

    attempts INT DEFAULT 0,

    max_attempts INT DEFAULT 3,

    next_run TIMESTAMP NULL,

    started_at TIMESTAMP NULL,

    finished_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX(status),
    INDEX(job_type),
    INDEX(next_run)

);

-- ===========================================================
-- USUÁRIOS
-- ===========================================================

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_users (

    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    glpi_user_id INT NOT NULL,

    phone VARCHAR(30) NULL,

    department VARCHAR(100) NULL,

    receive_whatsapp TINYINT(1) DEFAULT 1,

    receive_ai TINYINT(1) DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE(glpi_user_id)

);

-- ===========================================================
-- CONFIGURAÇÕES GERAIS
-- ===========================================================

SET FOREIGN_KEY_CHECKS = 1;

COMMIT;