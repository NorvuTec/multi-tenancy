<?php

namespace Norvutec\MultiTenancyBundle\Exception;

class TenantNotFoundException extends MultiTenancyException {

    public function __construct(string $tenantSubdomain)
    {
        parent::__construct("Tenant $tenantSubdomain not found", 404);
    }

}