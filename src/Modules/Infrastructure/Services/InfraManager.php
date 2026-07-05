<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\Modules\Infrastructure\Entities\InfraNode;
use SOLPI\Modules\Infrastructure\Entities\InfraEdge;
use SOLPI\Modules\Infrastructure\Repositories\InfraGraphRepository;
use Ramsey\Uuid\Uuid;

/**
 * Orquestrador central da SIIP.
 * Gerencia a entrada de novos dados de descoberta no Digital Twin.
 */
final class InfraManager
{
    private InfraGraphRepository $repository;

    public function __construct()
    {
        $this->repository = new InfraGraphRepository();
    }

    /**
     * Registra ou atualiza um ativo na plataforma de inteligência.
     */
    public function registerAsset(string $class, string $label, ?string $externalId = null, array $metadata = []): string
    {
        // Se já temos um UUID para este ID externo, recuperamos ele
        // Caso contrário, geramos um novo
        $uuid = Uuid::uuid4()->toString();

        // TODO: Implementar busca por external_id para manter consistência de UUID

        $node = new InfraNode($uuid, $class, $label, $externalId, $metadata);
        $this->repository->upsertNode($node);

        return $uuid;
    }

    /**
     * Vincula dois nós de infraestrutura.
     */
    public function connect(string $sourceUuid, string $targetUuid, string $relation, float $confidence = 1.0, string $protocol = 'manual'): bool
    {
        $edge = new InfraEdge($sourceUuid, $targetUuid, $relation, $confidence, $protocol);
        return $this->repository->saveEdge($edge);
    }
}
