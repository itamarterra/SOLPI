<?php

declare(strict_types=1);

namespace SOLPI\Licenses\Entities;

use DateTime;

final class License
{
    private ?int $id = null;

    private string $uuid;

    private string $name;

    private string $serial;

    private ?string $vendor = null;

    private ?string $version = null;

    private ?string $category = null;

    private ?string $purchaseDate = null;

    private ?string $expirationDate = null;

    private ?float $value = null;

    private ?int $companyId = null;

    private ?int $assetId = null;

    private bool $active = true;

    private array $metadata = [];

    private DateTime $createdAt;

    private DateTime $updatedAt;

    public function __construct(
        string $uuid,
        string $name,
        string $serial
    ){
        $this->uuid = $uuid;
        $this->name = $name;
        $this->serial = $serial;

        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function id(): ?int { return $this->id; }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function uuid(): string { return $this->uuid; }

    public function name(): string { return $this->name; }

    public function serial(): string { return $this->serial; }

    public function setVendor(?string $vendor): self
    {
        $this->vendor = $vendor;
        return $this;
    }

    public function vendor(): ?string
    {
        return $this->vendor;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function setPurchaseDate(?string $date): self
    {
        $this->purchaseDate = $date;
        return $this;
    }

    public function purchaseDate(): ?string
    {
        return $this->purchaseDate;
    }

    public function setExpirationDate(?string $date): self
    {
        $this->expirationDate = $date;
        return $this;
    }

    public function expirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function setValue(?float $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function value(): ?float
    {
        return $this->value;
    }

    public function setCompanyId(?int $id): self
    {
        $this->companyId = $id;
        return $this;
    }

    public function companyId(): ?int
    {
        return $this->companyId;
    }

    public function setAssetId(?int $id): self
    {
        $this->assetId = $id;
        return $this;
    }

    public function assetId(): ?int
    {
        return $this->assetId;
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

    public function active(): bool
    {
        return $this->active;
    }

    public function setMetadata(
        string $key,
        mixed $value
    ): self{

        $this->metadata[$key]=$value;

        return $this;

    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [

            'id'=>$this->id,
            'uuid'=>$this->uuid,
            'name'=>$this->name,
            'serial'=>$this->serial,
            'vendor'=>$this->vendor,
            'version'=>$this->version,
            'category'=>$this->category,
            'purchase_date'=>$this->purchaseDate,
            'expiration_date'=>$this->expirationDate,
            'value'=>$this->value,
            'company_id'=>$this->companyId,
            'asset_id'=>$this->assetId,
            'active'=>$this->active,
            'metadata'=>$this->metadata,
            'created_at'=>$this->createdAt->format('Y-m-d H:i:s'),
            'updated_at'=>$this->updatedAt->format('Y-m-d H:i:s')

        ];
    }
}
