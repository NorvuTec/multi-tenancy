<?php

namespace Norvutec\MultiTenancyBundle\DataCollector;

use Norvutec\MultiTenancyBundle\Entity\Tenant;
use Norvutec\MultiTenancyBundle\Service\MultiTenancyService;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantProfilerCollector extends AbstractDataCollector {

    public function __construct(
        private readonly MultiTenancyService $multiTenancyService
    )
    {}

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void {

    }

    public function getCurrentTenant(): ?Tenant {
        return $this->multiTenancyService->getCurrentTenant();
    }

    public function getTenantName(): string
    {
        return $this->getCurrentTenant()?->getIdentifier() ?: "--";
    }

}