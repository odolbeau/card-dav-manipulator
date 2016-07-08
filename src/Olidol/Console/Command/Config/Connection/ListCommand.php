<?php

namespace Olidol\Console\Command\Config\Connection;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sabre\DAV\Client;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Olidol\Console\Command\ConfigurationCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends ConfigurationCommand
{
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct('connections', $logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function doConfigure()
    {
        $this
            ->setName('config:connection:list')
            ->setDescription('List all existing connections.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $connections = $this->loadConfig();

        if (0 === count($connections)) {
            $io->warning('No connections defined.');

            return 1;
        }

        $rows = [];
        foreach ($connections as $name => $connection) {
            $rows[] = [$name, $connection ['baseUri'], $connection['username'], str_pad('', strlen($connection['password']), '*')];
        }

        $io->table(['Name', 'baseUrl', 'username', 'password'], $rows);
    }
}
