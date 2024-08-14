<?php

namespace Norvutec\MultiTenancyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class MultiTenancyApplication extends Application {

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $inputDefinition->addOption(new InputOption("--tenant", null, InputOption::VALUE_REQUIRED, "The identifier of the tenant", 'default'));
        return $inputDefinition;
    }

}