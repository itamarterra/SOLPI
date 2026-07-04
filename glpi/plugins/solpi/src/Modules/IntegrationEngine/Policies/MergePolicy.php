<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Policies;

final class MergePolicy
{
    /**
     * @var array<int,string>
     */
    private array $protectedFields = [
        'company.tax_id',
        'user.email',
        'asset.serial',
        'license.product_key',
        'document.sha256',
    ];

    public function isProtected(string $fieldPath): bool
    {
        return in_array($fieldPath, $this->protectedFields, true);
    }
}
