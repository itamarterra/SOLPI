<?php
declare(strict_types=1);

namespace SOLPI\Documents\Repositories;

use SOLPI\Documents\Entities\Document;
use SOLPI\Core\BaseRepository;

final class DocumentRepository extends BaseRepository
{
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
        $query = "SELECT * FROM `glpi_solpi_documents` WHERE 1=1";

        foreach ($filters as $key => $value) {
            $query .= " AND `{$key}` = '{$value}'";
        }

        $result = $this->db->query($query);
        $documents = [];

        while ($row = $result->fetch_assoc()) {
            $documents[] = $this->hydrate($row);
        }

        return $documents;
    }

    /**
     * @return int
     */
    public function getTotalStorageSize(): int
    {
        $query = "SELECT SUM(size) as total FROM `glpi_solpi_documents`";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        
        return $row['total'] ?? 0;
    }

    /**
     * @param array<string,mixed> $row
     * @return Document
     */
    private function hydrate(array $row): Document
    {
        return new Document(
            $row['id'],
            $row['filename'],
            $row['path'],
            $row['size']
        );
    }

    /**
     * @param Document $document
     * @return Document
     */
    public function save(Document $document): Document
    {
        $query = "INSERT INTO `glpi_solpi_documents` (id, filename, path, size) VALUES (?, ?, ?, ?)";
        // Prepared statement would go here
        return $document;
    }
}

