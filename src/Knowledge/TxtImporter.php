<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class TxtImporter implements ImporterInterface
{
    public function import(
        string $content
    ): string {

        return trim($content);

    }
}