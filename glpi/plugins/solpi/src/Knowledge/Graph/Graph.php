<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Graph;

final class Graph
{
    private array $nodes = [];
    private array $edges = [];

    public function addNode(Node $node): void
    {
        $this->nodes[$node->id] = $node;
    }

    public function addEdge(Edge $edge): void
    {
        $this->edges[] = $edge;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getEdges(): array
    {
        return $this->edges;
    }
}

