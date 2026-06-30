<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

final class IntentDetector
{
    public function detect(
        string $question
    ): string {

        $question = strtoupper($question);

        if(str_contains($question,'QUANTOS'))
            return 'COUNT';

        if(str_contains($question,'LISTA'))
            return 'LIST';

        if(str_contains($question,'MOSTRE'))
            return 'LIST';

        if(str_contains($question,'PROCURE'))
            return 'SEARCH';

        if(str_contains($question,'BUSQUE'))
            return 'SEARCH';

        if(str_contains($question,'QUAL'))
            return 'QUESTION';

        return 'GENERAL';

    }
}
