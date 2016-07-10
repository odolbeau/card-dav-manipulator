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

class DeleteCommand extends Command
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
            ->setName('config:connection:delete')
            ->setDescription('Delete an existing solution.')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the connection.')
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

        if (!array_key_exists($name, $connections)) {
            $io->error("Connection $name does not exist.");

            return 1;
        }

        unset($connections[$name]);

        $this->config->save('connections', $connections);

        $io->success("Connection $name deleted.");
    }
}
