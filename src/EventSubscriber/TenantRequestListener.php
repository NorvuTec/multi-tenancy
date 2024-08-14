<?php

namespace Norvutec\MultiTenancyBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Norvutec\MultiTenancyBundle\Doctrine\DBAL\TenantConnectionInterface;
use Norvutec\MultiTenancyBundle\Entity\Tenant;
use Norvutec\MultiTenancyBundle\Exception\TenantConnectionException;
use Norvutec\MultiTenancyBundle\Exception\TenantNotFoundException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listener for Kernel requests to change the tenant database connection
 * based on the subdomain of the request
 *
 * @package Norvutec\MultiTenancyBundle\EventSubscriber
 */
readonly class TenantRequestListener {

    public function __construct(
        private EntityManagerInterface    $defaultEntityManager,
        private TenantConnectionInterface $tenantConnection,
        private string                    $tenantClass
    ) { }

    /**
     * @throws TenantConnectionException
     * @throws TenantNotFoundException
     */
    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            // Skip non main requests
            return;
        }

        $subdomain = $this->getSubdomain($event->getRequest()->getHost());
        if($subdomain == null) {
            // Nothing to do if the subdomain is empty
            return;
        }
        $this->loadTenant($subdomain);
    }

    /**
     * @throws TenantNotFoundException
     * @throws TenantConnectionException
     */
    #[AsEventListener(event: ConsoleEvents::COMMAND)]
    public function onConsoleCommand(ConsoleCommandEvent $event): void {
        if(!$event->getInput()->hasOption("tenant")) {
            return;
        }
        $tenant = $event->getInput()->getOption("tenant");
        if($tenant == null) {
            // Option not set
            return;
        }
        $this->loadTenant($tenant);
    }

    /**
     * @throws TenantNotFoundException
     * @throws TenantConnectionException
     */
    private function loadTenant(string $subdomain): void {
        /** @var Tenant $tenant */
        $tenant = $this->defaultEntityManager->getRepository($this->tenantClass)
            ->findOneBy(array("identifier" => $subdomain));
        if($tenant == null) {
            throw new TenantNotFoundException($subdomain);
        }

        try{
            $this->tenantConnection->getDriverConnection();
            $this->tenantConnection->useTenant($tenant);
        }catch (\Throwable $e) {
            throw new TenantConnectionException($tenant->getIdentifier(), $e);
        }
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
        if((count($exploded) >= 2)) {
            return explode('.', $hostname)[0];
        }
        return null;
    }

}
