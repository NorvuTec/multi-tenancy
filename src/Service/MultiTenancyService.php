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

    /**
     * @param list<string> $reservedSubdomains
     */
    public function __construct(
        private EntityManagerInterface      $defaultEntityManager,
        private TenantConnectionInterface   $tenantConnection,
        private string                      $tenantClass,
        private string                      $baseDomain = 'novt.online',
        private array                       $reservedSubdomains = ['www', 'login', 'hub', 'app', 'mail'],
    ) {
        $this->baseDomain = strtolower(ltrim($this->baseDomain, '.'));
    }

    public function getBaseDomain(): string
    {
        return $this->baseDomain;
    }

    /**
     * Loads the current tenant by the request
     * @param Request $request request to process
     *
     * @throws MultiTenancyException
     */
    public function loadTenantByRequest(Request $request): void {
        $host = strtolower($request->getHost());
        $identifier = $this->resolveIdentifierFromHost($host);
        if ($identifier !== null) {
            $this->loadTenant($identifier);
            return;
        }

        $tenant = $this->findTenantByCustomDomain($host);
        if ($tenant !== null) {
            $this->activateTenant($tenant);
        }
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
     * Resolve platform subdomain identifier from host, or null if apex/reserved/custom.
     */
    public function resolveIdentifierFromHost(string $hostname): ?string
    {
        $hostname = strtolower(explode(':', $hostname)[0]);
        if ($hostname === $this->baseDomain || $hostname === 'www.'.$this->baseDomain) {
            return null;
        }

        $suffix = '.'.$this->baseDomain;
        if (!str_ends_with($hostname, $suffix)) {
            return null;
        }

        $prefix = substr($hostname, 0, -strlen($suffix));
        if ($prefix === '' || str_contains($prefix, '.')) {
            // Multi-level under base (e.g. a.b.base) — use first label as identifier
            $parts = explode('.', $prefix);
            $subdomain = $parts[0] ?: null;
        } else {
            $subdomain = $prefix;
        }

        if ($subdomain === null || in_array($subdomain, $this->reservedSubdomains, true)) {
            return null;
        }

        return $subdomain;
    }

    /**
     * Cache/log directory key for a host (identifier or stable custom-domain key).
     */
    public function resolveCacheKeyFromHost(string $hostname): ?string
    {
        $identifier = $this->resolveIdentifierFromHost($hostname);
        if ($identifier !== null) {
            return $identifier;
        }

        $hostname = strtolower(explode(':', $hostname)[0]);
        if ($hostname === $this->baseDomain || $hostname === 'www.'.$this->baseDomain) {
            return null;
        }

        if (str_ends_with($hostname, '.'.$this->baseDomain)) {
            return null;
        }

        return 'cdn_'.preg_replace('/[^a-z0-9]+/', '_', $hostname);
    }

    /**
     * @throws MultiTenancyException
     */
    private function loadTenant(string $subdomain): void {
        /** @var Tenant|null $tenant */
        $tenant = $this->defaultEntityManager->getRepository($this->tenantClass)
            ->findOneBy(array("identifier" => $subdomain));
        if($tenant == null) {
            throw new TenantNotFoundException($subdomain);
        }
        $this->activateTenant($tenant);
    }

    /**
     * @throws MultiTenancyException
     */
    private function activateTenant(Tenant $tenant): void
    {
        if(!$tenant->canBeLoaded()) {
            throw new TenantNotEnabledException($tenant->getIdentifier() ?? '');
        }

        try{
            $this->tenantConnection->getDriverConnection();
            $this->tenantConnection->useTenant($tenant);
        }catch (\Throwable $e) {
            throw new TenantConnectionException($tenant->getIdentifier() ?? '', $e);
        }
        $this->currentTenant = $tenant;
    }

    private function findTenantByCustomDomain(string $hostname): ?Tenant
    {
        $repo = $this->defaultEntityManager->getRepository($this->tenantClass);
        if (method_exists($repo, 'findOneByActiveCustomDomain')) {
            /** @var Tenant|null $tenant */
            $tenant = $repo->findOneByActiveCustomDomain($hostname);
            return $tenant;
        }

        // Fallback if repository lacks helper (field must exist on entity)
        try {
            /** @var Tenant|null $tenant */
            $tenant = $repo->findOneBy([
                'customDomain' => $hostname,
                'customDomainStatus' => 'active',
            ]);
            return $tenant;
        } catch (\Throwable) {
            return null;
        }
    }

}
