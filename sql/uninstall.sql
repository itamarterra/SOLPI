/*
|--------------------------------------------------------------------------
| SOLPI Professional — Uninstall
|--------------------------------------------------------------------------
| Remove todas as tabelas do plugin em ordem segura.
|--------------------------------------------------------------------------
*/

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `glpi_plugin_solpi_logs`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_notifications`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_webhooks`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_jobs`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_dashboard`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_knowledge_relationships`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_knowledge_entities`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_knowledge`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_embeddings`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_conversations`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_ai`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_whatsapp`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_tickets`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_alerts`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_settings`;
DROP TABLE IF EXISTS `glpi_plugin_solpi_config`;

SET FOREIGN_KEY_CHECKS = 1;
