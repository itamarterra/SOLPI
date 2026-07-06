<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

/**
 * Analisa o contexto dos dados para identificar a intenção (Usuários, Tickets, Ativos).
 */
final class CognitiveIntentService
{
    public function detect(array $headers): string
    {
        $h = array_map('strtolower', $headers);
        $score = ['user' => 0, 'ticket' => 0, 'asset' => 0];

        // Pesos para Usuários
        if ($this->hasMatch($h, ['mail', 'email', 'login', 'user', 'usuario', 'nome', 'sobrenome', 'cargo', 'departamento'])) {
            $score['user'] += 5;
        }

        // Pesos para Tickets
        if ($this->hasMatch($h, ['problema', 'chamado', 'ticket', 'erro', 'falha', 'incidente', 'descricao', 'assunto'])) {
            $score['ticket'] += 5;
        }

        // Pesos para Ativos
        if ($this->hasMatch($h, ['serial', 'patrimonio', 'modelo', 'fabricante', 'marca', 'asset', 'computador', 'monitor'])) {
            $score['asset'] += 5;
        }

        arsort($score);
        return array_key_first($score);
    }

    private function hasMatch(array $headers, array $keywords): bool
    {
        foreach ($keywords as $word) {
            foreach ($headers as $head) {
                if (str_contains($head, $word)) return true;
            }
        }
        return false;
    }
}
