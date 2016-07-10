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

class ListCommand extends Command
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
            ->setName('config:connection:list')
            ->setDescription('List all existing connections.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $connections = $this->config->load('connections');

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
