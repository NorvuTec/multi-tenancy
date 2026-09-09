<?php

namespace Norvutec\MultiTenancyBundle;

use Norvutec\MultiTenancyBundle\DependencyInjection\MultiTenancyExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class NorvutecMultiTenancyBundle extends AbstractBundle {

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MultiTenancyExtension();
    }


    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}