<?php

namespace Norvutec\MultiTenancyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints\Unique;

/**
 * Superclass for the tenant entity
 * Contains the required data to connect to the tenant's datasource
 */
#[MappedSuperclass]
class Tenant {

    #[Unique]
    #[ORM\Column(length: 50)]
    private ?string $identifier = null;

    #[ORM\Column(nullable: false)]
    private bool $enabled = false;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serverIp = null;

    #[Ignore]
    #[ORM\Column(nullable: true)]
    private ?int $databasePort = null;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $databaseName = null;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $databaseUser = null;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $databasePassword = null;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    #[Ignore]
    public function getServerIp(): ?string
    {
        return $this->serverIp;
    }

    #[Ignore]
    public function setServerIp(?string $serverIp): self
    {
        $this->serverIp = $serverIp;
        return $this;
    }

    #[Ignore]
    public function getDatabasePort(): ?int
    {
        return $this->databasePort;
    }

    #[Ignore]
    public function setDatabasePort(?int $databasePort): self
    {
        $this->databasePort = $databasePort;
        return $this;
    }

    #[Ignore]
    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    #[Ignore]
    public function setDatabaseName(?string $databaseName): self
    {
        $this->databaseName = $databaseName;
        return $this;
    }

    #[Ignore]
    public function getDatabaseUser(): ?string
    {
        return $this->databaseUser;
    }

    #[Ignore]
    public function setDatabaseUser(?string $databaseUser): self
    {
        $this->databaseUser = $databaseUser;
        return $this;
    }

    #[Ignore]
    public function getDatabasePassword(): ?string
    {
        return $this->databasePassword;
    }

    #[Ignore]
    public function setDatabasePassword(?string $databasePassword): self
    {
        $this->databasePassword = $databasePassword;
        return $this;
    }

}