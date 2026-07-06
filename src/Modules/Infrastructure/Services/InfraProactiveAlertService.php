<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\Core\Config;
use SOLPI\Integrations\Evolution\EvolutionClient;
use SOLPI\Modules\Infrastructure\Services\InfraAIInsightService;
use Throwable;

/**
 * Serviço responsável por enviar alertas proativos para o WhatsApp.
 */
final class InfraProactiveAlertService
{
    private Config $config;
    private EvolutionClient $whatsapp;

    public function __construct()
    {
        $this->config = new Config();
        $this->config->load();
        $this->whatsapp = new EvolutionClient($this->config->get('config.evolution', []));
    }

    /**
     * Envia o alerta.
     */
    public function sendExecutiveAlert(?string $phoneNumber = null): array
    {
        if (!$this->whatsapp->isEnabled()) {
            return ['success' => false, 'message' => 'WhatsApp não habilitado no config.php.'];
        }

        $target = $phoneNumber ?? (string)getenv('SOLPI_DIRECTOR_PHONE');
        if (empty($target)) {
            return ['success' => false, 'message' => 'Número do destinatário não informado.'];
        }

        try {
            // Tenta gerar via IA
            $aiService = new InfraAIInsightService();
            $insight = $aiService->generateExecutiveSummary();
        } catch (Throwable $e) {
            // Fallback caso a IA falhe ou não tenha API Key
            $db = \SOLPI\Core\Database\DatabaseManager::getInstance();
            $total = $db->table('glpi_plugin_solpi_inframap_nodes')->count();
            $insight = "A varredura foi concluída. Atualmente temos {$total} ativos mapeados no seu Digital Twin. (Nota: IA offline no momento).";
        }

        $message = "🤖 *SOLPI INTELLIGENCE REPORT*\n\n" . $insight;

        $response = $this->whatsapp->sendText($target, $message);

        if (!($response['success'] ?? false)) {
            $err = $response['error'] ?? ($response['message'] ?? 'Erro na Evolution API');
            if (isset($response['status_code']) && $response['status_code'] === 401) {
                $err = "Token da Evolution API Inválido (Unauthorized).";
            }
            return ['success' => false, 'message' => $err];
        }

        return ['success' => true];
    }
}
