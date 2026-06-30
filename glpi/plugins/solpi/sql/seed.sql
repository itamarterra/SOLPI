/*
|--------------------------------------------------------------------------
| SOLPI Professional
|--------------------------------------------------------------------------
| Seed Database
|--------------------------------------------------------------------------
| Version: 2.0.0-alpha
|--------------------------------------------------------------------------
*/

START TRANSACTION;

-- ===========================================================
-- CONFIGURAÇÃO PADRÃO
-- ===========================================================

INSERT INTO glpi_plugin_solpi_config (

    zabbix_url,

    zabbix_token,

    evolution_url,

    evolution_token,

    ai_provider,

    ai_model,

    ai_api_key,

    timezone,

    language,

    enabled

)

VALUES (

    '',

    '',

    '',

    '',

    'openai',

    'gpt-5.5',

    '',

    'America/Sao_Paulo',

    'pt_BR',

    1

);

-- ===========================================================
-- DASHBOARD
-- ===========================================================

INSERT INTO glpi_plugin_solpi_dashboard (metric, metric_value)

VALUES

('tickets_open','0'),

('tickets_closed','0'),

('alerts_received','0'),

('alerts_resolved','0'),

('whatsapp_sent','0'),

('whatsapp_received','0'),

('ai_requests','0'),

('knowledge_articles','0'),

('users_online','0'),

('system_uptime','100');

-- ===========================================================
-- WEBHOOKS
-- ===========================================================

INSERT INTO glpi_plugin_solpi_webhooks (

source,

endpoint,

secret,

status

)

VALUES

(

'Zabbix',

'/plugins/solpi/ajax/webhook.php',

'',

1

);

COMMIT;