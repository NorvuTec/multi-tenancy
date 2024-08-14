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

    public function load(array $configs, ContainerBuilder $container) {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
        $container->setParameter('multi_tenancy.tenant_class', $configs[0]['tenant_class']);
    }

}