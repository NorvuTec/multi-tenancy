<?php

namespace Norvutec\MultiTenancyBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Norvutec\MultiTenancyBundle\Doctrine\DBAL\TenantConnectionInterface;
use Norvutec\MultiTenancyBundle\Entity\Tenant;
use Norvutec\MultiTenancyBundle\Exception\TenantConnectionException;
use Norvutec\MultiTenancyBundle\Exception\TenantNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

readonly class TenantRequestListener implements EventSubscriberInterface {

    public function __construct(
        private EntityManagerInterface    $defaultEntityManager,
        private TenantConnectionInterface $tenantConnection,
        private string                    $tenantClass
    ) { }

    /**
     * @throws TenantConnectionException
     * @throws TenantNotFoundException
     */
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

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest'
        ];
    }


}
