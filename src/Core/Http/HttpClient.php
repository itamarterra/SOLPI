<?php

declare(strict_types=1);

namespace SOLPI\Core\Http;

use SOLPI\Core\Http\Contracts\TransportInterface;

final class HttpClient
{
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function send(HttpRequest $request): HttpResponse
    {
        return $this->transport->send($request);
    }
}
