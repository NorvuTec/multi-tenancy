<?php

namespace Norvutec\MultiTenancyBundle\Doctrine\DBAL;

use Norvutec\MultiTenancyBundle\Entity\Tenant;

interface TenantConnectionInterface {

    public function useTenant(Tenant $tenant): void;

    public function getDriverConnection(): string;

}