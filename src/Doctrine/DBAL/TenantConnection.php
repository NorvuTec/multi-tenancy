<?php

namespace Norvutec\MultiTenancyBundle\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Norvutec\MultiTenancyBundle\Entity\Tenant;

/**
 * Implements the special DBAL connection for tenants
 * This class is used to switch the connection to the tenant's database
 *
 * @package Norvutec\MultiTenancyBundle\Doctrine\DBAL
 */
class TenantConnection extends Connection implements TenantConnectionInterface {

    public function useTenant(Tenant $tenant): void {
        if ($this->isConnected()) {
            $this->close();
        }
        if($tenant->getDatabaseName() != null) {
            $params['dbname'] = $tenant->getDatabaseName();
        }
        if($tenant->getServerIp() != null) {
            $params['host'] = $tenant->getServerIp();
        }
        if($tenant->getDatabasePort() != null) {
            $params['port'] = $tenant->getDatabasePort();
        }
        if($tenant->getDatabaseUser() != null) {
            $params['user'] = $tenant->getDatabaseUser();
        }
        if($tenant->getDatabasePassword() != null) {
            $params['password'] = $tenant->getDatabasePassword();
        }
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