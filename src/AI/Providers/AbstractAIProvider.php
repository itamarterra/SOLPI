<?php

declare(strict_types=1);

namespace SOLPI\AI\Providers;

abstract class AbstractAIProvider implements AIProviderInterface
{
    protected string $apiKey='';

    protected string $model='';

    protected string $endpoint='';

    public function setApiKey(
        string $key
    ): static{

        $this->apiKey=$key;

        return $this;

    }

    public function setModel(
        string $model
    ): static{

        $this->model=$model;

        return $this;

    }

    public function setEndpoint(
        string $endpoint
    ): static{

        $this->endpoint=$endpoint;

        return $this;

    }

    public function available(): bool
    {
        return $this->apiKey!=='';
    }
}
