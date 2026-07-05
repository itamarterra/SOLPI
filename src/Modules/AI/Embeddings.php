<?php

declare(strict_types=1);

namespace SOLPI\Modules\AI;

use SOLPI\AI\Providers\ProviderFactory;
use SOLPI\Core\Database\DatabaseManager;
use RuntimeException;

/**
 * Service for managing and generating text embeddings
 */
final class Embeddings
{
    private ProviderFactory $factory;
    private DatabaseManager $db;

    public function __construct()
    {
        $this->factory = new ProviderFactory();
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * Generates a vector for the given text
     *
     * @return array<int,float>
     */
    public function generate(string $text): array
    {
        $provider = $this->factory->createDefault();
        return $provider->embedding($text);
    }

    /**
     * Saves an embedding for a specific item (Ticket, Article, etc)
     */
    public function store(string $itemType, int $itemId, array $vector): bool
    {
        $conn = $this->db->getConnection();

        $data = [
            'source_type' => $itemType,
            'source_id'   => $itemId,
            'embedding'   => json_encode($vector),
            'model'       => $this->factory->createDefault()->name()
        ];

        $existing = $this->db->table('glpi_plugin_solpi_embeddings')
            ->where(['source_type' => $itemType, 'source_id' => $itemId])
            ->first();

        if ($existing) {
            return $conn->update('glpi_plugin_solpi_embeddings', $data, ['id' => $existing['id']]);
        }

        return (bool)$conn->insert('glpi_plugin_solpi_embeddings', $data);
    }

    /**
     * Retrieves an embedding from the database
     *
     * @return array<int,float>|null
     */
    public function get(string $itemType, int $itemId): ?array
    {
        $row = $this->db->table('glpi_plugin_solpi_embeddings')
            ->where(['source_type' => $itemType, 'source_id' => $itemId])
            ->first();

        if ($row && isset($row['embedding'])) {
            return json_decode($row['embedding'], true);
        }

        return null;
    }
}
