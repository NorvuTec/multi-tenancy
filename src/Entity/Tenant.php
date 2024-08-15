<?php

namespace Norvutec\MultiTenancyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints\Unique;

/**
 * Interface for the tenant entity
 * Contains the required data to connect to the tenant's datasource
 */
interface Tenant {

    public function getIdentifier(): ?string;

    public function isEnabled(): bool;

    public function getServerIp(): ?string;

    public function getDatabasePort(): ?int;

    public function getDatabaseName(): ?string;

    public function getDatabaseUser(): ?string;

    public function getDatabasePassword(): ?string;

}