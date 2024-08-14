<?php

namespace Norvutec\MultiTenancyBundle\Exception;

class TenantNotFoundException extends \Exception {

    public function __construct(string $tenantSubdomain)
    {
        parent::__construct("Tenant $tenantSubdomain not found", 404);
    }

}