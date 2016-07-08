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

class CreateCommand extends ConfigurationCommand
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
            ->setName('config:connection:create')
            ->setDescription('Create a new connection or overwrite an existing one.')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the connection.')
            ->addArgument('baseUri', InputArgument::REQUIRED, 'Url.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username to use to connect.')
            ->addArgument('password', InputArgument::REQUIRED, 'Password to use to connect.')
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

        $connections[$name] = [
            'baseUri'  => $input->getArgument('baseUri'),
            'username' => $input->getArgument('username'),
            'password' => $input->getArgument('password'),
        ];

        $this->saveConfig($connections);

        $io->success("New connection named $name added.");
    }
}
