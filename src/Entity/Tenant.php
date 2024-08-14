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

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    #[Ignore]
    public function getServerIp(): ?string
    {
        return $this->serverIp;
    }

    #[Ignore]
    public function setServerIp(?string $serverIp): void
    {
        $this->serverIp = $serverIp;
    }

    #[Ignore]
    public function getDatabasePort(): ?int
    {
        return $this->databasePort;
    }

    #[Ignore]
    public function setDatabasePort(?int $databasePort): void
    {
        $this->databasePort = $databasePort;
    }

    #[Ignore]
    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    #[Ignore]
    public function setDatabaseName(?string $databaseName): void
    {
        $this->databaseName = $databaseName;
    }

    #[Ignore]
    public function getDatabaseUser(): ?string
    {
        return $this->databaseUser;
    }

    #[Ignore]
    public function setDatabaseUser(?string $databaseUser): void
    {
        $this->databaseUser = $databaseUser;
    }

    #[Ignore]
    public function getDatabasePassword(): ?string
    {
        return $this->databasePassword;
    }

    #[Ignore]
    public function setDatabasePassword(?string $databasePassword): void
    {
        $this->databasePassword = $databasePassword;
    }

}