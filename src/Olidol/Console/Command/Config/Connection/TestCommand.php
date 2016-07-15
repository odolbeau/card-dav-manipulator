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
use Sabre\HTTP\ClientHttpException;
use Olidol\ClientFactory;

class TestCommand extends Command
{
    protected $clientFactory;
    protected $logger;

    /**
     * __construct
     *
     * @param ClientFactory $clientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(ClientFactory $clientFactory, LoggerInterface $logger = null)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger ?: new NullLogger();

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:connection:test')
            ->setDescription('Test an existing connection.')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the connection to test.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name');

        $client = $this->clientFactory->getClientForConnection($name);

        try {
            $client->options();

            $io->success("Connection $name successful.");
        } catch (ClientHttpException $e) {
            $io->error("Connection $name invalid.");

            throw $e;
        }
    }
}
