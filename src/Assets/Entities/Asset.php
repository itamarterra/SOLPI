<?php

declare(strict_types=1);

namespace SOLPI\Assets\Entities;

use DateTime;

final class Asset
{
    private ?int $id = null;

    private string $uuid;

    private string $name;

    private string $type;

    private ?string $manufacturer = null;

    private ?string $model = null;

    private ?string $serial = null;

    private ?string $assetTag = null;

    private ?int $companyId = null;

    private ?int $userId = null;

    private ?string $location = null;

    private ?string $purchaseDate = null;

    private ?string $warrantyDate = null;

    private bool $active = true;

    /**
     * Metadata bag for the asset.
     *
     * @var array<string,mixed>
     */
    private array $metadata = [];

    private DateTime $createdAt;

    private DateTime $updatedAt;

    public function __construct(
        string $uuid,
        string $name,
        string $type
    ) {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->type = strtoupper($type);

        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);
        return $this;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = strtoupper($type);
        return $this;
    }

    public function manufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    public function model(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function serial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(?string $serial): self
    {
        $this->serial = $serial;
        return $this;
    }

    public function assetTag(): ?string
    {
        return $this->assetTag;
    }

    public function setAssetTag(?string $assetTag): self
    {
        $this->assetTag = $assetTag;
        return $this;
    }

    public function companyId(): ?int
    {
        return $this->companyId;
    }

    public function setCompanyId(?int $companyId): self
    {
        $this->companyId = $companyId;
        return $this;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function purchaseDate(): ?string
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?string $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    public function warrantyDate(): ?string
    {
        return $this->warrantyDate;
    }

    public function setWarrantyDate(?string $warrantyDate): self
    {
        $this->warrantyDate = $warrantyDate;
        return $this;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function activate(): self
    {
        $this->active = true;
        return $this;
    }

    public function deactivate(): self
    {
        $this->active = false;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function metadata(): array
    {
        /** @var array<string,mixed> $m */
        $m = $this->metadata;

        return $m;
    }

    public function setMetadata(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * Convert entity to array representation.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        /** @var array<string,mixed> $md */
        $md = $this->metadata;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'type' => $this->type,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'serial' => $this->serial,
            'asset_tag' => $this->assetTag,
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'location' => $this->location,
            'purchase_date' => $this->purchaseDate,
            'warranty_date' => $this->warrantyDate,
            'active' => $this->active,
            'metadata' => $md,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
