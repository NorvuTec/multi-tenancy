<?php

namespace Norvutec\MultiTenancyBundle\DependencyInjection;

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Extension for the MultiTenancyBundle
 *
 * @package Norvutec\MultiTenancyBundle\DependencyInjection
 */
class MultiTenancyExtension extends Extension {

    public function load(array $configs, ContainerBuilder $container): void {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
        if(isset($configs[0]['tenant_class'])) {
            $container->setParameter('multi_tenancy.tenant_class', $configs[0]['tenant_class']);
        }
        if(isset($configs[0]['tenant_select_route'])) {
            $container->setParameter('multi_tenancy.tenant_select_route', $configs[0]['tenant_select_route']);
        }
    }

}