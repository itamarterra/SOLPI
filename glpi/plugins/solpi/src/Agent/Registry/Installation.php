<?php

declare(strict_types=1);

namespace SOLPI\Agent\Registry;

use SOLPI\Core\BaseEntity;

final class Installation extends BaseEntity
{
    private string $siteName;

    private ?string $siteUrl;

    private ?string $glpiVersion;

    private ?string $solpiVersion;

    private ?string $ipAddress;

    private array $capabilities = [];

    private ?array $inventory = null;

    private string $status = 'offline';

    private ?string $authToken = null;

    public function __construct(string $uuid, string $siteName)
    {
        parent::__construct($uuid);

        $this->siteName = $siteName;
    }

    public function setSiteUrl(?string $u): static { $this->siteUrl=$u; return $this; }
    public function setGlpiVersion(?string $v): static { $this->glpiVersion=$v; return $this; }
    public function setSolpiVersion(?string $v): static { $this->solpiVersion=$v; return $this; }
    public function setIpAddress(?string $ip): static { $this->ipAddress=$ip; return $this; }
    public function setCapabilities(array $c): static { $this->capabilities=$c; return $this; }
    public function setInventory(?array $i): static { $this->inventory=$i; return $this; }
    public function setStatus(string $s): static { $this->status=$s; return $this; }
    public function setAuthToken(?string $t): static { $this->authToken=$t; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'uuid' => $this->uuid(),
            'site_name' => $this->siteName,
            'site_url' => $this->siteUrl,
            'glpi_version' => $this->glpiVersion,
            'solpi_version' => $this->solpiVersion,
            'ip_address' => $this->ipAddress,
            'capabilities' => $this->capabilities,
            'inventory' => $this->inventory,
            'status' => $this->status,
            'auth_token' => $this->authToken,
            'created_at' => $this->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
