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
use Symfony\Component\Console\Style\SymfonyStyle;
use Olidol\Config;

class CreateCommand extends Command
{
    protected $config;
    protected $logger;

    /**
     * __construct
     *
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger ?: new NullLogger();

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $connections = $this->config->load('connections');

        $name = $input->getArgument('name');

        $connections[$name] = [
            'baseUri'  => $input->getArgument('baseUri'),
            'username' => $input->getArgument('username'),
            'password' => $input->getArgument('password'),
        ];

        $this->config->save('connections', $connections);

        $io->success("New connection named $name added.");
    }
}
