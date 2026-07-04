<?php

declare(strict_types=1);

namespace SOLPI\Core\Http\Contracts;

use SOLPI\Core\Http\HttpRequest;
use SOLPI\Core\Http\HttpResponse;

interface TransportInterface
{
    public function send(
        HttpRequest $request
    ): HttpResponse;
}