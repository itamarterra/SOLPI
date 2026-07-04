<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Transport;

final class CurlRequestBuilder
{
    private $ch;

    public function __construct()
    {
        $this->ch = curl_init();
    }

    public function setUrl(string $url): self
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    public function setMethod(string $method): self
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "$key: $value";
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $formatted);
        return $this;
    }

    public function setPayload(string $payload): self
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $payload);
        return $this;
    }

    public function build()
    {
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        return $this->ch;
    }
}

