<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\AI\Providers\ProviderFactory;
use SOLPI\Modules\Infrastructure\Repositories\InfraGraphRepository;

/**
 * Módulo de Inteligência Artificial para Consultas de Infraestrutura.
 * Permite interagir com o Digital Twin usando Linguagem Natural.
 */
final class InfraAIQueryService
{
    private DatabaseManager $db;
    private ProviderFactory $aiFactory;
    private InfraGraphRepository $repository;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->aiFactory = new ProviderFactory();
        $this->repository = new InfraGraphRepository();
    }

    /**
     * Responde a uma pergunta sobre a infraestrutura.
     */
    public function ask(string $question): string
    {
        // 1. Extrai o contexto relevante do Grafo (Digital Twin)
        $context = $this->getInfraContext();

        // 2. Prepara o Prompt para a IA com a "Foto" da infraestrutura
        $prompt = $this->buildPrompt($question, $context);

        // 3. Chama o provedor de IA (OpenAI/Gemini/Ollama)
        $provider = $this->aiFactory->createDefault();

        return $provider->chat($prompt, [
            'temperature' => 0.2, // Baixa temperatura para respostas técnicas precisas
            'max_tokens'  => 500
        ]);
    }

    /**
     * Constrói uma representação textual simplificada do Digital Twin para a IA.
     */
    private function getInfraContext(): string
    {
        $nodes = iterator_to_array($this->db->table('glpi_plugin_solpi_inframap_nodes')->limit(100)->get());
        $edges = iterator_to_array($this->db->table('glpi_plugin_solpi_inframap_edges')->limit(200)->get());

        $context = "ESTADO ATUAL DA INFRAESTRUTURA (DIGITAL TWIN):\n";

        foreach ($nodes as $node) {
            $context .= "- ENTIDADE: {$node['label']} (Tipo: {$node['class']}, ID: {$node['uuid']})\n";
        }

        foreach ($edges as $edge) {
            $context .= "- RELACIONAMENTO: {$edge['source_uuid']} --[{$edge['relation_type']}]--> {$edge['target_uuid']}\n";
        }

        return $context;
    }

    /**
     * Monta o prompt final unindo a pergunta do usuário ao contexto do sistema.
     */
    private function buildPrompt(string $question, string $context): string
    {
        return <<<PROMPT
Você é o Especialista de Infraestrutura do SOLPI.
Sua missão é responder perguntas técnicas baseadas no DIGITAL TWIN da empresa fornecido abaixo.

REGRAS:
1. Use apenas as informações do CONTEXTO para responder.
2. Se não souber a resposta ou ela não estiver no contexto, diga que não possui essa informação mapeada.
3. Seja direto, técnico e profissional.
4. Identifique dependências e riscos se a pergunta for sobre impacto.

CONTEXTO:
$context

PERGUNTA DO USUÁRIO:
$question

RESPOSTA DO SOLPI:
PROMPT;
    }
}
