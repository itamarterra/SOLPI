CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255),
    document VARCHAR(50),
    email VARCHAR(255),
    phone VARCHAR(50),
    website VARCHAR(255),
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    active TINYINT(1) DEFAULT 1,
    settings JSON NULL,
    metadata JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_company_name(name),
    INDEX idx_company_uuid(uuid)
);

CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    department VARCHAR(100),
    position VARCHAR(100),
    active TINYINT(1) DEFAULT 1,
    settings JSON NULL,
    metadata JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_user_company(company_id),
    INDEX idx_user_name(name),
    CONSTRAINT fk_solpi_user_company
        FOREIGN KEY (company_id)
        REFERENCES glpi_plugin_solpi_companies(id)
        ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT NULL,
    user_id INT NULL,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100),
    manufacturer VARCHAR(100),
    model VARCHAR(255),
    serial VARCHAR(255),
    asset_tag VARCHAR(255),
    location VARCHAR(255),
    purchase_date DATE,
    warranty_date DATE,
    active TINYINT(1) DEFAULT 1,
    metadata JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_asset_company(company_id),
    INDEX idx_asset_user(user_id),
    INDEX idx_asset_serial(serial),
    CONSTRAINT fk_solpi_asset_company
        FOREIGN KEY (company_id)
        REFERENCES glpi_plugin_solpi_companies(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_solpi_asset_user
        FOREIGN KEY (user_id)
        REFERENCES glpi_plugin_solpi_users(id)
        ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT NULL,
    asset_id INT NULL,
    name VARCHAR(255) NOT NULL,
    serial VARCHAR(255),
    vendor VARCHAR(255),
    version VARCHAR(100),
    category VARCHAR(100),
    purchase_date DATE,
    expiration_date DATE,
    value DECIMAL(12,2),
    active TINYINT(1) DEFAULT 1,
    metadata JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_license_company(company_id),
    INDEX idx_license_asset(asset_id),
    CONSTRAINT fk_solpi_license_company
        FOREIGN KEY (company_id)
        REFERENCES glpi_plugin_solpi_companies(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_solpi_license_asset
        FOREIGN KEY (asset_id)
        REFERENCES glpi_plugin_solpi_assets(id)
        ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    contract_number VARCHAR(100),
    start_date DATE,
    end_date DATE,
    value DECIMAL(15,2),
    status VARCHAR(50),
    metadata JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,

    INDEX idx_contract_company(company_id),

    CONSTRAINT fk_contract_company
        FOREIGN KEY(company_id)
        REFERENCES glpi_plugin_solpi_companies(id)
        ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    company_id INT,
    asset_id INT NULL,
    contract_id INT NULL,

    title VARCHAR(255),

    filename VARCHAR(255),

    extension VARCHAR(20),

    filesize BIGINT,

    mime VARCHAR(150),

    path TEXT,

    checksum VARCHAR(255),

    metadata JSON NULL,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_document_company(company_id),

    INDEX idx_document_asset(asset_id),

    INDEX idx_document_contract(contract_id),

    CONSTRAINT fk_document_company
        FOREIGN KEY(company_id)
        REFERENCES glpi_plugin_solpi_companies(id),

    CONSTRAINT fk_document_asset
        FOREIGN KEY(asset_id)
        REFERENCES glpi_plugin_solpi_assets(id),

    CONSTRAINT fk_document_contract
        FOREIGN KEY(contract_id)
        REFERENCES glpi_plugin_solpi_contracts(id)
);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_tickets (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    glpi_ticket_id INT,

    company_id INT,

    asset_id INT,

    user_id INT,

    title VARCHAR(255),

    description LONGTEXT,

    status VARCHAR(50),

    priority VARCHAR(30),

    category VARCHAR(100),

    ai_summary LONGTEXT,

    metadata JSON,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_ticket_company(company_id),

    INDEX idx_ticket_asset(asset_id),

    INDEX idx_ticket_user(user_id),

    CONSTRAINT fk_ticket_company
        FOREIGN KEY(company_id)
        REFERENCES glpi_plugin_solpi_companies(id),

    CONSTRAINT fk_ticket_asset
        FOREIGN KEY(asset_id)
        REFERENCES glpi_plugin_solpi_assets(id),

    CONSTRAINT fk_ticket_user
        FOREIGN KEY(user_id)
        REFERENCES glpi_plugin_solpi_users(id)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_knowledge_entities (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    entity_type VARCHAR(100),

    entity_uuid CHAR(36),

    payload JSON,

    embedding LONGTEXT,

    created_at DATETIME,

    INDEX idx_entity_type(entity_type),

    INDEX idx_entity_uuid(entity_uuid)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_knowledge_relationships (

    id INT AUTO_INCREMENT PRIMARY KEY,

    source_uuid CHAR(36),

    target_uuid CHAR(36),

    relation VARCHAR(100),

    weight FLOAT DEFAULT 1,

    metadata JSON,

    created_at DATETIME,

    INDEX idx_source(source_uuid),

    INDEX idx_target(target_uuid)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_memory (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    title VARCHAR(255),

    content LONGTEXT,

    embedding LONGTEXT,

    metadata JSON,

    created_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_conversations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) NOT NULL UNIQUE,

    company_id INT NULL,

    user_id INT NULL,

    title VARCHAR(255),

    provider VARCHAR(50),

    model VARCHAR(100),

    status VARCHAR(30),

    tokens_input INT DEFAULT 0,

    tokens_output INT DEFAULT 0,

    metadata JSON,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_ai_company(company_id),

    INDEX idx_ai_user(user_id),

    CONSTRAINT fk_ai_company
        FOREIGN KEY(company_id)
        REFERENCES glpi_plugin_solpi_companies(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_ai_user
        FOREIGN KEY(user_id)
        REFERENCES glpi_plugin_solpi_users(id)
        ON DELETE SET NULL

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_messages (

    id INT AUTO_INCREMENT PRIMARY KEY,

    conversation_id INT NOT NULL,

    role VARCHAR(20),

    content LONGTEXT,

    prompt_tokens INT,

    completion_tokens INT,

    metadata JSON,

    created_at DATETIME,

    INDEX idx_message_conversation(conversation_id),

    CONSTRAINT fk_ai_message_conversation
        FOREIGN KEY(conversation_id)
        REFERENCES glpi_plugin_solpi_ai_conversations(id)
        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_embeddings (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    entity_type VARCHAR(100),

    entity_uuid CHAR(36),

    provider VARCHAR(50),

    model VARCHAR(100),

    embedding LONGTEXT,

    checksum VARCHAR(64),

    created_at DATETIME,

    INDEX idx_embedding_entity(entity_uuid),

    INDEX idx_embedding_type(entity_type)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_prompts (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255),

    description TEXT,

    prompt LONGTEXT,

    version VARCHAR(20),

    active TINYINT(1) DEFAULT 1,

    created_at DATETIME,

    updated_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_models (

    id INT AUTO_INCREMENT PRIMARY KEY,

    provider VARCHAR(50),

    model VARCHAR(100),

    endpoint VARCHAR(255),

    api_version VARCHAR(50),

    temperature DECIMAL(3,2),

    max_tokens INT,

    active TINYINT(1),

    created_at DATETIME,

    updated_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_functions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    conversation_id INT,

    function_name VARCHAR(255),

    arguments JSON,

    response LONGTEXT,

    execution_time FLOAT,

    success TINYINT(1),

    created_at DATETIME,

    INDEX idx_function_conversation(conversation_id),

    CONSTRAINT fk_ai_function_conversation
        FOREIGN KEY(conversation_id)
        REFERENCES glpi_plugin_solpi_ai_conversations(id)
        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    level VARCHAR(20),

    provider VARCHAR(50),

    model VARCHAR(100),

    message LONGTEXT,

    metadata JSON,

    created_at DATETIME,

    INDEX idx_log_level(level),

    INDEX idx_log_provider(provider)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_integrations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    name VARCHAR(150),

    provider VARCHAR(100),

    base_url VARCHAR(500),

    api_key TEXT,

    api_secret TEXT,

    username VARCHAR(255),

    password TEXT,

    active TINYINT(1) DEFAULT 1,

    settings JSON,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_provider(provider),

    INDEX idx_name(name)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_sync_queue (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    integration_id INT,

    entity VARCHAR(100),

    entity_uuid CHAR(36),

    operation VARCHAR(50),

    payload JSON,

    priority INT DEFAULT 5,

    status VARCHAR(30),

    attempts INT DEFAULT 0,

    next_execution DATETIME,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_queue_status(status),

    INDEX idx_queue_priority(priority),

    CONSTRAINT fk_sync_integration

        FOREIGN KEY(integration_id)

        REFERENCES glpi_plugin_solpi_integrations(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_sync_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    queue_id INT,

    success TINYINT(1),

    execution_time FLOAT,

    message LONGTEXT,

    response LONGTEXT,

    created_at DATETIME,

    INDEX idx_sync_queue(queue_id),

    CONSTRAINT fk_sync_log_queue

        FOREIGN KEY(queue_id)

        REFERENCES glpi_plugin_solpi_sync_queue(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_scheduler_jobs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(255),

    handler VARCHAR(255),

    cron_expression VARCHAR(100),

    enabled TINYINT(1),

    last_execution DATETIME,

    next_execution DATETIME,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_scheduler_enabled(enabled)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_scheduler_history (

    id INT AUTO_INCREMENT PRIMARY KEY,

    job_id INT,

    success TINYINT(1),

    duration FLOAT,

    output LONGTEXT,

    created_at DATETIME,

    INDEX idx_scheduler_job(job_id),

    CONSTRAINT fk_scheduler_history

        FOREIGN KEY(job_id)

        REFERENCES glpi_plugin_solpi_scheduler_jobs(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_notifications (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    user_id INT,

    title VARCHAR(255),

    message LONGTEXT,

    channel VARCHAR(50),

    status VARCHAR(30),

    metadata JSON,

    created_at DATETIME,

    INDEX idx_notification_user(user_id),

    CONSTRAINT fk_notification_user

        FOREIGN KEY(user_id)

        REFERENCES glpi_plugin_solpi_users(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_webhooks (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(255),

    url TEXT,

    secret VARCHAR(255),

    events JSON,

    active TINYINT(1),

    created_at DATETIME,

    updated_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_api_keys (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(255),

    api_key VARCHAR(255),

    permissions JSON,

    last_used DATETIME,

    expires_at DATETIME,

    active TINYINT(1),

    created_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_graph_nodes (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) NOT NULL UNIQUE,

    entity_type VARCHAR(100) NOT NULL,

    entity_id INT NOT NULL,

    label VARCHAR(255),

    description TEXT,

    metadata JSON,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_graph_entity(entity_type),

    INDEX idx_graph_entityid(entity_id)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_graph_edges (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    source_node INT,

    target_node INT,

    relation VARCHAR(100),

    weight FLOAT DEFAULT 1,

    confidence FLOAT DEFAULT 1,

    metadata JSON,

    created_at DATETIME,

    INDEX idx_edge_source(source_node),

    INDEX idx_edge_target(target_node),

    CONSTRAINT fk_edge_source

        FOREIGN KEY(source_node)

        REFERENCES glpi_plugin_solpi_graph_nodes(id)

        ON DELETE CASCADE,

    CONSTRAINT fk_edge_target

        FOREIGN KEY(target_node)

        REFERENCES glpi_plugin_solpi_graph_nodes(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_tags (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(150),

    color VARCHAR(20),

    description TEXT,

    created_at DATETIME,

    UNIQUE(name)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_entity_tags (

    id INT AUTO_INCREMENT PRIMARY KEY,

    tag_id INT,

    entity_uuid CHAR(36),

    entity_type VARCHAR(100),

    created_at DATETIME,

    INDEX idx_tag(tag_id),

    INDEX idx_entity(entity_uuid),

    CONSTRAINT fk_entity_tag

        FOREIGN KEY(tag_id)

        REFERENCES glpi_plugin_solpi_tags(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_ai_memory (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    conversation_uuid CHAR(36),

    entity_uuid CHAR(36),

    summary LONGTEXT,

    embedding LONGTEXT,

    importance FLOAT DEFAULT 0,

    created_at DATETIME,

    INDEX idx_memory_entity(entity_uuid),

    INDEX idx_memory_conversation(conversation_uuid)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_knowledge_index (

    id INT AUTO_INCREMENT PRIMARY KEY,

    entity_uuid CHAR(36),

    keyword VARCHAR(255),

    occurrences INT DEFAULT 1,

    created_at DATETIME,

    INDEX idx_keyword(keyword),

    INDEX idx_entity_uuid(entity_uuid)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_entity_history (

    id INT AUTO_INCREMENT PRIMARY KEY,

    entity_uuid CHAR(36),

    entity_type VARCHAR(100),

    action VARCHAR(50),

    payload JSON,

    user_id INT,

    created_at DATETIME,

    INDEX idx_history_entity(entity_uuid),

    INDEX idx_history_type(entity_type),

    CONSTRAINT fk_history_user

        FOREIGN KEY(user_id)

        REFERENCES glpi_plugin_solpi_users(id)

        ON DELETE SET NULL

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_inventory_imports (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    company_id INT,

    source VARCHAR(100),

    filename VARCHAR(255),

    importer VARCHAR(100),

    total_rows INT DEFAULT 0,

    imported_rows INT DEFAULT 0,

    duplicated_rows INT DEFAULT 0,

    ignored_rows INT DEFAULT 0,

    status VARCHAR(50),

    metadata JSON,

    created_at DATETIME,

    finished_at DATETIME,

    INDEX idx_import_company(company_id),

    CONSTRAINT fk_import_company
        FOREIGN KEY(company_id)
        REFERENCES glpi_plugin_solpi_companies(id)
        ON DELETE SET NULL

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_import_files (

    id INT AUTO_INCREMENT PRIMARY KEY,

    import_id INT,

    original_name VARCHAR(255),

    stored_name VARCHAR(255),

    extension VARCHAR(20),

    mime VARCHAR(100),

    checksum VARCHAR(64),

    filesize BIGINT,

    created_at DATETIME,

    INDEX idx_import_file(import_id),

    CONSTRAINT fk_import_file

        FOREIGN KEY(import_id)

        REFERENCES glpi_plugin_solpi_inventory_imports(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_import_rows (

    id INT AUTO_INCREMENT PRIMARY KEY,

    import_id INT,

    line_number INT,

    raw_data LONGTEXT,

    parsed_data JSON,

    entity_type VARCHAR(100),

    entity_uuid CHAR(36),

    imported TINYINT(1),

    created_at DATETIME,

    INDEX idx_import_rows(import_id),

    INDEX idx_import_entity(entity_uuid),

    CONSTRAINT fk_import_rows

        FOREIGN KEY(import_id)

        REFERENCES glpi_plugin_solpi_inventory_imports(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_import_errors (

    id INT AUTO_INCREMENT PRIMARY KEY,

    import_id INT,

    row_id INT,

    message LONGTEXT,

    severity VARCHAR(30),

    created_at DATETIME,

    INDEX idx_error_import(import_id),

    CONSTRAINT fk_import_error

        FOREIGN KEY(import_id)

        REFERENCES glpi_plugin_solpi_inventory_imports(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_duplicate_entities (

    id INT AUTO_INCREMENT PRIMARY KEY,

    entity_uuid CHAR(36),

    duplicate_uuid CHAR(36),

    entity_type VARCHAR(100),

    confidence FLOAT,

    resolved TINYINT(1),

    created_at DATETIME,

    INDEX idx_duplicate_entity(entity_uuid),

    INDEX idx_duplicate_type(entity_type)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_match_rules (

    id INT AUTO_INCREMENT PRIMARY KEY,

    entity_type VARCHAR(100),

    field_name VARCHAR(100),

    comparison VARCHAR(50),

    weight FLOAT,

    active TINYINT(1),

    created_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_asset_movements (

    id INT AUTO_INCREMENT PRIMARY KEY,

    asset_id INT,

    company_id INT,

    user_id INT,

    previous_location VARCHAR(255),

    current_location VARCHAR(255),

    movement_type VARCHAR(100),

    notes TEXT,

    created_at DATETIME,

    INDEX idx_asset_move(asset_id),

    CONSTRAINT fk_asset_move

        FOREIGN KEY(asset_id)

        REFERENCES glpi_plugin_solpi_assets(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_asset_warranty (

    id INT AUTO_INCREMENT PRIMARY KEY,

    asset_id INT,

    provider VARCHAR(255),

    contract_number VARCHAR(255),

    start_date DATE,

    end_date DATE,

    status VARCHAR(50),

    metadata JSON,

    created_at DATETIME,

    INDEX idx_asset_warranty(asset_id),

    CONSTRAINT fk_asset_warranty

        FOREIGN KEY(asset_id)

        REFERENCES glpi_plugin_solpi_assets(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_asset_maintenance (

    id INT AUTO_INCREMENT PRIMARY KEY,

    asset_id INT,

    maintenance_type VARCHAR(100),

    description TEXT,

    technician VARCHAR(255),

    cost DECIMAL(12,2),

    performed_at DATE,

    next_maintenance DATE,

    created_at DATETIME,

    INDEX idx_asset_maintenance(asset_id),

    CONSTRAINT fk_asset_maintenance

        FOREIGN KEY(asset_id)

        REFERENCES glpi_plugin_solpi_assets(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_audit_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    user_id INT NULL,

    entity_type VARCHAR(100),

    entity_id INT,

    action VARCHAR(100),

    old_data JSON,

    new_data JSON,

    ip_address VARCHAR(45),

    user_agent TEXT,

    created_at DATETIME,

    INDEX idx_audit_entity(entity_type),

    INDEX idx_audit_user(user_id),

    CONSTRAINT fk_audit_user
        FOREIGN KEY(user_id)
        REFERENCES glpi_plugin_solpi_users(id)
        ON DELETE SET NULL

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_login_history (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,

    login_at DATETIME,

    logout_at DATETIME,

    ip_address VARCHAR(45),

    user_agent TEXT,

    success TINYINT(1),

    failure_reason VARCHAR(255),

    INDEX idx_login_user(user_id),

    CONSTRAINT fk_login_user
        FOREIGN KEY(user_id)
        REFERENCES glpi_plugin_solpi_users(id)
        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_api_requests (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    api_key_id INT,

    endpoint VARCHAR(255),

    method VARCHAR(20),

    request LONGTEXT,

    response LONGTEXT,

    status_code INT,

    execution_time FLOAT,

    ip_address VARCHAR(45),

    created_at DATETIME,

    INDEX idx_api_key(api_key_id),

    INDEX idx_endpoint(endpoint),

    CONSTRAINT fk_api_request_key
        FOREIGN KEY(api_key_id)
        REFERENCES glpi_plugin_solpi_api_keys(id)
        ON DELETE SET NULL

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_roles (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(100) UNIQUE,

    description TEXT,

    system_role TINYINT(1) DEFAULT 0,

    created_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_roles (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(100) UNIQUE,

    description TEXT,

    system_role TINYINT(1) DEFAULT 0,

    created_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_permissions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    permission_key VARCHAR(150) UNIQUE,

    description TEXT,

    created_at DATETIME

);CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_permissions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    permission_key VARCHAR(150) UNIQUE,

    description TEXT,

    created_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_role_permissions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    role_id INT,

    permission_id INT,

    created_at DATETIME,

    UNIQUE(role_id,permission_id),

    CONSTRAINT fk_role_permission_role
        FOREIGN KEY(role_id)
        REFERENCES glpi_plugin_solpi_roles(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_role_permission_permission
        FOREIGN KEY(permission_id)
        REFERENCES glpi_plugin_solpi_permissions(id)
        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_user_roles (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,

    role_id INT,

    created_at DATETIME,

    UNIQUE(user_id,role_id),

    CONSTRAINT fk_user_role_user
        FOREIGN KEY(user_id)
        REFERENCES glpi_plugin_solpi_users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_user_role_role
        FOREIGN KEY(role_id)
        REFERENCES glpi_plugin_solpi_roles(id)
        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_security_events (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    severity VARCHAR(20),

    category VARCHAR(100),

    title VARCHAR(255),

    description LONGTEXT,

    source VARCHAR(100),

    resolved TINYINT(1) DEFAULT 0,

    metadata JSON,

    created_at DATETIME,

    INDEX idx_security_severity(severity),

    INDEX idx_security_category(category)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_settings (

    id INT AUTO_INCREMENT PRIMARY KEY,

    setting_key VARCHAR(150) NOT NULL UNIQUE,

    setting_value LONGTEXT,

    setting_type VARCHAR(50),

    category VARCHAR(100),

    description TEXT,

    is_public TINYINT(1) DEFAULT 0,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_setting_category(category)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_user_preferences (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    preference_key VARCHAR(150),

    preference_value LONGTEXT,

    created_at DATETIME,

    updated_at DATETIME,

    UNIQUE(user_id,preference_key),

    CONSTRAINT fk_preference_user

        FOREIGN KEY(user_id)

        REFERENCES glpi_plugin_solpi_users(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_dashboard_widgets (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36) UNIQUE,

    user_id INT,

    widget_type VARCHAR(100),

    title VARCHAR(255),

    position_x INT,

    position_y INT,

    width INT,

    height INT,

    configuration JSON,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_dashboard_user(user_id),

    CONSTRAINT fk_dashboard_user

        FOREIGN KEY(user_id)

        REFERENCES glpi_plugin_solpi_users(id)

        ON DELETE CASCADE

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_reports (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    name VARCHAR(255),

    description TEXT,

    sql_query LONGTEXT,

    parameters JSON,

    created_by INT,

    created_at DATETIME,

    updated_at DATETIME,

    INDEX idx_report_creator(created_by),

    CONSTRAINT fk_report_creator

        FOREIGN KEY(created_by)

        REFERENCES glpi_plugin_solpi_users(id)

        ON DELETE SET NULL

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_kpi_history (

    id INT AUTO_INCREMENT PRIMARY KEY,

    kpi_name VARCHAR(255),

    value DECIMAL(18,4),

    reference_date DATE,

    metadata JSON,

    created_at DATETIME,

    INDEX idx_kpi_name(kpi_name),

    INDEX idx_kpi_date(reference_date)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_system_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    level VARCHAR(30),

    component VARCHAR(150),

    message LONGTEXT,

    context JSON,

    created_at DATETIME,

    INDEX idx_system_level(level),

    INDEX idx_system_component(component)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_versions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    version VARCHAR(30),

    database_version VARCHAR(30),

    installed_at DATETIME,

    updated_at DATETIME

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_background_jobs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    uuid CHAR(36),

    job_type VARCHAR(100),

    payload JSON,

    status VARCHAR(50),

    progress DECIMAL(5,2),

    started_at DATETIME,

    finished_at DATETIME,

    created_at DATETIME,

    INDEX idx_background_status(status),

    INDEX idx_background_type(job_type)

);
CREATE TABLE IF NOT EXISTS glpi_plugin_solpi_feature_flags (

    id INT AUTO_INCREMENT PRIMARY KEY,

    feature_key VARCHAR(150) UNIQUE,

    enabled TINYINT(1),

    description TEXT,

    created_at DATETIME,

    updated_at DATETIME

);