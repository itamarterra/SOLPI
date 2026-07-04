<?php
declare(strict_types=1);

namespace SOLPI\Documents\Services;

use Ramsey\Uuid\Uuid;
use SOLPI\Documents\Entities\Document;
use SOLPI\Documents\Repositories\DocumentRepository;

final class DocumentService
{
    private DocumentRepository $repository;

    public function __construct()
    {
        $this->repository = new DocumentRepository();
    }

    /**
     * @param array<string,mixed> $metadata
     * @return Document
     */
    public function upload(string $filename, string $path, array $metadata = []): Document
    {
        $document = new Document(
            Uuid::uuid4()->toString(),
            $filename,
            $path,
            filesize($path) ?: 0
        );

        return $this->repository->save($document);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<int,Document>
     */
    public function find(array $filters = []): array
    {
        return $this->repository->findBy($filters);
    }

    /**
     * @return int
     */
    public function getTotalSize(): int
    {
        return $this->repository->getTotalStorageSize();
    }
}

