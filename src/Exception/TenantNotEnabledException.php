<?php

namespace Norvutec\MultiTenancyBundle\Exception;

/**
 * Exception thrown when a tenant is not enabled
 */
class TenantNotEnabledException extends MultiTenancyException {

    public function __construct(string $identifier) {
        parent::__construct("Tenant $identifier is not enabled");
    }

}