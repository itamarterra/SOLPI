<?php
declare(strict_types=1);

namespace SOLPI\Documents\Repositories;

use SOLPI\Documents\Entities\Document;
use SOLPI\Core\BaseRepository;
use SOLPI\Core\Database\QueryBuilder;

final class DocumentRepository extends BaseRepository
{
    protected string $table = 'glpi_solpi_documents';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<int,Document>
     */
    public function findBy(array $filters = []): array
    {
        $qb = new QueryBuilder($this->db);
        $rows = $qb->from($this->table)
                   ->where($filters)
                   ->execute();

        $documents = [];
        foreach ($rows as $row) {
            $documents[] = $this->hydrate($row);
        }

        return $documents;
    }

    /**
     * @return int
     */
    public function getTotalStorageSize(): int
    {
        $qb = new QueryBuilder($this->db);
        $row = $qb->from($this->table)
                  ->select(['SUM(size) as total'])
                  ->first();
        
        return (int)($row['total'] ?? 0);
    }

    /**
     * @param array<string,mixed> $row
     * @return Document
     */
    private function hydrate(array $row): Document
    {
        return new Document(
            (int)$row['id'],
            (string)$row['filename'],
            (string)$row['path'],
            (int)$row['size']
        );
    }

    /**
     * @param Document $document
     * @return Document
     */
    public function save(Document $document): Document
    {
        $data = [
            'filename' => $document->filename,
            'path'     => $document->path,
            'size'     => $document->size,
        ];

        if ($document->id > 0) {
            $this->update($document->id, $data);
            return $document;
        }

        $id = $this->insert($data);
        return new Document(
            $id,
            $document->filename,
            $document->path,
            $document->size
        );
    }
}

