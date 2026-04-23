<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DemoCommand extends Command
{
    protected static $defaultName = 'demo';

    private LicensingService $licensingService;

    public function __construct(LicensingService $licensingService)
    {
        $this->licensingService = $licensingService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Activating license...');
        if ($this->licensingService->activate()) {
            $output->writeln('License activated successfully!');
        } else {
            $output->writeln('License activation failed!');
            return Command::FAILURE;
        }

        $output->writeln('Validating license...');
        if ($this->licensingService->validate()) {
            $output->writeln('License is valid!');
        } else {
            $output->writeln('License is invalid!');
            return Command::FAILURE;
        }

        $output->writeln('Reporting activity...');
        $this->licensingService->reportActivity('app_started', 'Demo application started');

        $output->writeln('Demo completed successfully!');
        return Command::SUCCESS;
    }
}