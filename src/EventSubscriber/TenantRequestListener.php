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
use Symfony\Component\Console\Input\InputOption;
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
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 100)]
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
    #[AsEventListener(event: ConsoleEvents::COMMAND, priority: 100)]
    public function onConsoleCommand(ConsoleCommandEvent $event): void {
        // Add --tenant to the application's global definition so it is available in ALL commands.
        // This replicates the deprecated Bundle::registerCommands() approach.
        // The option is added here (before Command::run() calls mergeApplicationDefinition()) so that
        // it is merged into every command's fullDefinition and accessible via $input->getOption('tenant')
        // inside execute().
        $application = $event->getCommand()?->getApplication();
        if ($application !== null && !$application->getDefinition()->hasOption('tenant')) {
            $application->getDefinition()->addOption(
                new InputOption('--tenant', null, InputOption::VALUE_OPTIONAL, 'The identifier of the tenant', null)
            );
        }

        // At event-fire time, the input is already bound to the command's native definition
        // (before mergeApplicationDefinition), so getOption() is not yet available for app-level options.
        // Use getParameterOption() to read the raw value from argv directly.
        $tenant = $event->getInput()->getParameterOption('--tenant', null);
        if (!is_string($tenant) || $tenant === '') {
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
