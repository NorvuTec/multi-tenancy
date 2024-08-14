<?php

namespace Norvutec\MultiTenancyBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Norvutec\MultiTenancyBundle\Entity\Tenant;

/**
 * Implements the special DBAL connection for tenants
 * This class is used to switch the connection to the tenant's database
 */
class TenantConnection extends Connection implements TenantConnectionInterface {

    public function useTenant(Tenant $tenant): void {
        if ($this->isConnected()) {
            $this->close();
        }
        $params['url'] = sprintf("mysql://%s:%s@%s:%s/%s",
            $tenant->getDatabaseUser(),
            $tenant->getDatabasePassword(),
            $tenant->getServerIp(),
            $tenant->getDatabasePort(),
            $tenant->getDatabaseName()
        );
        $params['dbname'] = $tenant->getDatabaseName();
        parent::__construct(
            $params,
            $this->_driver,
            $this->_config,
            $this->_eventManager
        );
    }

    public function getDriverConnection(): string
    {
        return $this->getDatabasePlatform()->getName();
    }

}