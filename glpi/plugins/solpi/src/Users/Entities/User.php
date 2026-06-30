<?php

declare(strict_types=1);

namespace SOLPI\Users\Entities;

use DateTime;

final class User
{
    private ?int $id = null;

    private string $uuid;

    private string $name;

    private ?string $email = null;

    private ?string $phone = null;

    private ?string $department = null;

    private ?string $position = null;

    private ?int $companyId = null;

    private bool $active = true;

    private array $settings = [];

    private DateTime $createdAt;

    private DateTime $updatedAt;

    public function __construct(
        string $uuid,
        string $name
    ){
        $this->uuid = $uuid;
        $this->name = $name;

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

    public function email(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function department(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function position(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;
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

    public function settings(): array
    {
        return $this->settings;
    }

    public function setSetting(
        string $key,
        mixed $value
    ): self{

        $this->settings[$key] = $value;

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

    public function toArray(): array
    {
        return [

            'id'=>$this->id,

            'uuid'=>$this->uuid,

            'name'=>$this->name,

            'email'=>$this->email,

            'phone'=>$this->phone,

            'department'=>$this->department,

            'position'=>$this->position,

            'company_id'=>$this->companyId,

            'active'=>$this->active,

            'settings'=>$this->settings,

            'created_at'=>$this->createdAt->format('Y-m-d H:i:s'),

            'updated_at'=>$this->updatedAt->format('Y-m-d H:i:s')

        ];
    }

}
