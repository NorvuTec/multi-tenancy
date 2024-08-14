<?php

namespace Norvutec\MultiTenancyBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Norvutec\MultiTenancyBundle\Attribute\NeedTenant;
use Norvutec\MultiTenancyBundle\Doctrine\DBAL\TenantConnectionInterface;
use Norvutec\MultiTenancyBundle\Entity\Tenant;
use Norvutec\MultiTenancyBundle\Exception\MultiTenancyException;
use Norvutec\MultiTenancyBundle\Exception\TenantConnectionException;
use Norvutec\MultiTenancyBundle\Exception\TenantNotFoundException;
use Norvutec\MultiTenancyBundle\Service\MultiTenancyService;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Listener for Kernel requests to change the tenant database connection
 * based on the subdomain of the request
 *
 * @package Norvutec\MultiTenancyBundle\EventSubscriber
 */
readonly class TenantRequestListener {

    public function __construct(
        private MultiTenancyService        $multiTenancyService,
        private readonly RouterInterface   $router,
        private string                     $tenantSelectRoute
    ) { }

    /**
     * @throws MultiTenancyException
     */
    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            // Skip non main requests
            return;
        }
        $this->multiTenancyService->loadTenantByRequest($event->getRequest());
    }

    /**
     * @throws MultiTenancyException
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
        $this->multiTenancyService->loadTenantByIdentifier($tenant);
    }

    #[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS)]
    public function onControllerArgRequest(ControllerArgumentsEvent $event): void {
        if(!is_array($attributes = $event->getAttributes()[NeedTenant::class] ?? null)) {
            return;
        }

        if($this->multiTenancyService->getCurrentTenant() == null) {
            $redirectUrl = $this->router->generate($this->tenantSelectRoute);
            $event->setController(function() use ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            });
        }
    }

}
