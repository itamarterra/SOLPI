<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\AI\Providers\ProviderFactory;
use Throwable;

/**
 * Analisador Universal de Intenção v3.0 - Suporte a Ativos e Inventário.
 */
final class CognitiveIntentService
{
    private ProviderFactory $aiFactory;

    public function __construct()
    {
        $this->aiFactory = new ProviderFactory();
    }

    public function detect(array $headers, array $sampleData = []): array
    {
        // 1. Tenta detecção rápida por palavras-chave
        $intent = $this->keywordDetect($headers);

        // 2. Se a IA estiver ativa, faz uma análise profunda
        try {
            $sample = json_encode(array_slice($sampleData, 0, 3));
            $prompt = "Analise estes cabeçalhos de TI: [" . implode(',', $headers) . "]. Amostra: $sample.
                       Qual a intenção? (user, ticket, asset).
                       - 'user': se for lista de pessoas, logins, e-mails de funcionários.
                       - 'ticket': se for lista de erros, problemas, reclamações de suporte.
                       - 'asset': se for lista de hardware, equipamentos, monitores, seriais, antivírus instalados.
                       Responda APENAS um JSON: {\"intent\": \"...\", \"reason\": \"...\", \"confidence\": 0-100}";

            $aiRes = json_decode($this->aiFactory->createDefault()->chat($prompt), true);
            if (isset($aiRes['intent'])) {
                return $aiRes;
            }
        } catch (Throwable) { }

        return ['intent' => $intent, 'reason' => 'Análise de padrão de colunas.', 'confidence' => 80];
    }

    private function keywordDetect(array $headers): string
    {
        $h = strtolower(implode(' ', $headers));
        // Ativos
        if ($this->containsAny($h, ['serial', 'patrimonio', 'modelo', 'fabricante', 'marca', 'asset', 'monitor', 'antivirus', 'hardware'])) return 'asset';
        // Usuários
        if ($this->containsAny($h, ['mail', 'email', 'login', 'user', 'usuario'])) return 'user';
        // Tickets
        if ($this->containsAny($h, ['erro', 'problema', 'ticket', 'incidente'])) return 'ticket';

        return 'asset'; // Default para inventário se for ambíguo
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($haystack, $n)) return true;
        }
        return false;
    }
}
