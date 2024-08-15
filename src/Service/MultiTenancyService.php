<?php

namespace Norvutec\MultiTenancyBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Norvutec\MultiTenancyBundle\Doctrine\DBAL\TenantConnectionInterface;
use Norvutec\MultiTenancyBundle\Entity\Tenant;
use Norvutec\MultiTenancyBundle\Exception\MultiTenancyException;
use Norvutec\MultiTenancyBundle\Exception\TenantConnectionException;
use Norvutec\MultiTenancyBundle\Exception\TenantNotEnabledException;
use Norvutec\MultiTenancyBundle\Exception\TenantNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class MultiTenancyService {

    private ?Tenant $currentTenant = null;

    public function __construct(
        private EntityManagerInterface      $defaultEntityManager,
        private TenantConnectionInterface   $tenantConnection,
        private string                      $tenantClass
    ) { }

    /**
     * Loads the current tenant by the request
     * @param Request $request request to process
     *
     * @throws MultiTenancyException
     */
    public function loadTenantByRequest(Request $request): void {
        $subdomain = $this->getSubdomain($request->getHost());
        if($subdomain == null) {
            // Nothing to do if the subdomain is empty
            return;
        }
        $this->loadTenant($subdomain);
    }

    /**
     * Loads the current tenant by its identifier
     * @param string $identifier identifier of the tenant
     *
     * @throws MultiTenancyException
     */
    public function loadTenantByIdentifier(string $identifier): void {
        $this->loadTenant($identifier);
    }

    /**
     * Returns the currently loaded tenant
     * @return Tenant|null
     */
    public function getCurrentTenant(): ?Tenant {
        return $this->currentTenant;
    }

    /**
     * Returns all tenants
     * @return array<Tenant>
     */
    public function getAllTenants(): array {
        return $this->defaultEntityManager->getRepository($this->tenantClass)->findAll();
    }

    /**
     * Returns all enabled tenants
     * @return array<Tenant>
     */
    public function getAllEnabledTenants(): array {
        return array_filter($this->getAllTenants(), fn(Tenant $tenant) => $tenant->isEnabled());
    }

    /**
     * @throws MultiTenancyException
     */
    private function loadTenant(string $subdomain): void {
        /** @var Tenant $tenant */
        $tenant = $this->defaultEntityManager->getRepository($this->tenantClass)
            ->findOneBy(array("identifier" => $subdomain));
        if($tenant == null) {
            throw new TenantNotFoundException($subdomain);
        }
        if(!$tenant->isEnabled()) {
            throw new TenantNotEnabledException($subdomain);
        }

        try{
            $this->tenantConnection->getDriverConnection();
            $this->tenantConnection->useTenant($tenant);
        }catch (\Throwable $e) {
            throw new TenantConnectionException($tenant->getIdentifier(), $e);
        }
        $this->currentTenant = $tenant;
    }

    /**
     * Returns the subdomain of the hostname if its existing
     *
     * @param string $hostname hostname to process
     * @return string|null subdomain if existing
     */
    private function getSubdomain(string $hostname) : ?string
    {
        $exploded = explode('.', $hostname);
        if((count($exploded) > 2)) {
            return explode('.', $hostname)[0];
        }
        return null;
    }
}