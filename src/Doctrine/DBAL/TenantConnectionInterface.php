<?php

namespace Norvutec\MultiTenancyBundle\Doctrine\DBAL;

use Norvutec\MultiTenancyBundle\Entity\Tenant;

/**
 * Interface for the Tenant database connection
 *
 * @package Norvutec\MultiTenancyBundle\Doctrine\DBAL
 */
interface TenantConnectionInterface {

    public function useTenant(Tenant $tenant): void;

    public function getDriverConnection(): string;

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array;

    /**
     * @param array<string, mixed> $params
     */
    public function restoreParams(array $params): void;

}