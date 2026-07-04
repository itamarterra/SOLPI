<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

interface ImporterInterface
{
    public function import(string $content): mixed;
}