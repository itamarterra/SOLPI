<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

final class Pagination
{
    private int $page;
    private int $perPage;
    private int $total;

    public function __construct(int $page = 1, int $perPage = 20, int $total = 0)
    {
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);
        $this->total = $total;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getTotalPages(): int
    {
        return (int)ceil($this->total / $this->perPage);
    }

    public function toArray(): array
    {
        return [
            'page'        => $this->page,
            'per_page'    => $this->perPage,
            'total'       => $this->total,
            'total_pages' => $this->getTotalPages()
        ];
    }
}

