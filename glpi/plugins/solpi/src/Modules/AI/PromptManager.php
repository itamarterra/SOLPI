<?php
declare(strict_types=1);

namespace SOLPI\Modules\AI;

final class PromptManager
{
    private array $templates = [
        'default' => "Você é o SOLPI, um assistente inteligente para GLPI.\nQuestão: {{question}}",
        'summary' => "Resuma o seguinte texto: {{text}}",
    ];

    public function get(string $name, array $vars = []): string
    {
        $template = $this->templates[$name] ?? $this->templates['default'];
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string)$value, $template);
        }
        return $template;
    }
}

