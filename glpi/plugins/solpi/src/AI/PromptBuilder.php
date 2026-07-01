<?php

declare(strict_types=1);

namespace SOLPI\AI;

final class PromptBuilder
{
    /**
     * @param array<int,array{payload:array<string,mixed>}> $documents
     */
    public function build(
        string $question,
        array $documents
    ): string {

        $prompt = "";

        $prompt .= "Você é a IA do SOLPI.\n\n";

        $prompt .= "Utilize apenas as informações abaixo.\n\n";

        foreach($documents as $document){

            $prompt .= json_encode(

                $document['payload'],

                JSON_PRETTY_PRINT|

                JSON_UNESCAPED_UNICODE

            );

            $prompt .= "\n\n";

        }

        $prompt .= "Pergunta:\n";

        $prompt .= $question;

        return $prompt;

    }
}
