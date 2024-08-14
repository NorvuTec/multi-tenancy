<?php

namespace Norvutec\MultiTenancyBundle;

use Norvutec\MultiTenancyBundle\DependencyInjection\MultiTenancyExtension;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class NorvutecMultiTenancyBundle extends AbstractBundle {

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MultiTenancyExtension();
    }

    public function registerCommands(Application $application): void {
        parent::registerCommands($application);
        $application->getDefinition()->addOption(new InputOption("--tenant", null, InputOption::VALUE_OPTIONAL, 'The identifier of the tenant', null));
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}