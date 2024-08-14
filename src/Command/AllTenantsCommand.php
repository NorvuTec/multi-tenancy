<?php

namespace Norvutec\MultiTenancyBundle\Command;

use Norvutec\MultiTenancyBundle\Entity\Tenant;
use Norvutec\MultiTenancyBundle\Exception\MultiTenancyException;
use Norvutec\MultiTenancyBundle\Service\MultiTenancyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AllTenantsCommand extends Command {

    public function __construct(
        protected readonly MultiTenancyService $multiTenancyService
    )
    {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("multitenancy:alltenants");
        $this->addOption("progress", null, InputOption::VALUE_NONE, "Enables the progress bar");
    }

    protected abstract function executeInTenant(InputInterface $input, OutputInterface $output): int;

    protected final function execute(InputInterface $input, OutputInterface $output): int
    {
        if($input->getOption("tenant") != null) {
            return $this->executeInTenant($input, $output);
        }
        $tenants = $this->multiTenancyService->getAllEnabledTenants();
        if(count($tenants) == 0) {
            $output->writeln("<error>No enabled tenants found to execute this command.</error>");
            return Command::FAILURE;
        }
        $useProgressBar = $input->hasOption("progress") && (bool)$input->getOption("progress");
        $progressBar = null;
        if ($useProgressBar) {
            $progressBar = new ProgressBar($output, count($tenants));
            $progressBar->setMessage("Executing command for tenants");
            $progressBar->start();
        }
        $allFailed = true;
        foreach($tenants as $tenant) {
            if($progressBar) {
                $progressBar->setMessage("Executing command for tenant " . $tenant->getIdentifier());
            }else{
                $output->writeln("<info>Executing command for tenant ".$tenant->getIdentifier()."</info>");
            }
            try {
                $this->multiTenancyService->loadTenantByIdentifier($tenant->getIdentifier());
                $this->executeInTenant($input, $output);
                $allFailed = false;
            }catch (MultiTenancyException $e) {
                $output->writeln("<error>Unable to switch to tenant".$tenant->getIdentifier().": ".$e->getMessage()."</error>");
            }catch (\Exception $e) {
                $output->writeln("<error>Unable to execute command for tenant ".$tenant->getIdentifier().": ".$e->getMessage()."</error>");
            }
            $progressBar?->advance();
        }
        $progressBar?->finish();
        return $allFailed ? Command::FAILURE : Command::SUCCESS;
    }

}