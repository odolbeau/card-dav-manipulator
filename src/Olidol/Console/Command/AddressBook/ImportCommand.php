<?php

namespace Olidol\Console\Command\AddressBook;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sabre\HTTP\ClientHttpException;
use Sabre\HTTP\Request;
use Olidol\ClientFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sabre\VObject;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportCommand extends Command
{
    protected $client;
    protected $logger;

    public function __construct(ClientFactory $clientFactory, LoggerInterface $logger = null)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger ?: new NullLogger();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('address-book:import')
            ->setDescription('Import an address book.')
            ->addArgument('connection', InputArgument::REQUIRED, 'Which connection to use?')
            ->addArgument('user', InputArgument::REQUIRED, 'The user who own the addressbook.')
            ->addArgument('addressbook', InputArgument::REQUIRED, 'Which addressbook should be filled?')
            ->addArgument('input', InputArgument::REQUIRED, 'A vcf file to import.')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of contacts to import.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $client = $this->clientFactory->getClientForConnection($input->getArgument('connection'));
        $client->setUser($input->getArgument('user'));
        $client->setAddressbook($input->getArgument('addressbook'));

        $inputFile = $input->getArgument('input');
        $limit = $input->getOption('limit', null);

        $io->text('Importing contacts...');

        $progressBar = new ProgressBar($output);
        $progressBar->setMessage('foobar');
        $progressBar->start();

        $contacts = 0;
        $splitter = new VObject\Splitter\VCard(fopen($inputFile, 'r'));
        while (null !== $vCard = $splitter->getNext()) {
            $client->updateContact($vCard);

            ++$contacts;
            $this->logger->debug('Contact {uid} created or updated.');

            $progressBar->advance();

            if (null !== $limit && $limit <= $contacts) {
                $this->logger->debug('Limit reached, stop immort.');

                break;
            }
        }

        $progressBar->finish();

        $io->success("$contacts contacts imported!");
    }
}
