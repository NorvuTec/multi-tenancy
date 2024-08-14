<?php

namespace Norvutec\MultiTenancyBundle\Exception;

use Throwable;

/**
 * Exception thrown when a tenant connection cannot be established
 */
class TenantConnectionException extends \Exception
{
    public function __construct(string $tenantIdentifier, Throwable $originalException)
    {
        parent::__construct("Error connecting to tenant $tenantIdentifier", $originalException->getCode(), $originalException);
    }

}