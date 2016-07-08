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

class DeleteCommand extends ConfigurationCommand
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
            ->setName('config:connection:delete')
            ->setDescription('Delete an existing solution.')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the connection.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $connections = $this->loadConfig();

        $name = $input->getArgument('name');

        if (!array_key_exists($name, $connections)) {
            $io->error("Connection $name does not exist.");

            return 1;
        }

        unset($connections[$name]);

        $this->saveConfig($connections);

        $io->success("Connection $name deleted.");
    }
}
