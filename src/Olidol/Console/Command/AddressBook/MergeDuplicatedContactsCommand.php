<?php

namespace Olidol\Console\Command\AddressBook;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Sabre\DAV\Client;
use Sabre\HTTP\ClientHttpException;
use Sabre\HTTP\Request;
use Olidol\ClientFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sabre\VObject;
use Symfony\Component\Console\Helper\ProgressBar;
use Olidol\DAV\Manipulator\DuplicateFinder;
use Olidol\DAV\Manipulator\Merger;

class MergeDuplicatedContactsCommand extends Command
{
    protected $clientFactory;
    protected $duplicateFinder;
    protected $merger;
    protected $logger;

    public function __construct(ClientFactory $clientFactory, DuplicateFinder $duplicateFinder, Merger $merger, LoggerInterface $logger = null)
    {
        $this->clientFactory = $clientFactory;
        $this->duplicateFinder = $duplicateFinder;
        $this->merger = $merger;
        $this->logger = $logger ?: new NullLogger();

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('address-book:merge-duplicates')
            ->setDescription('Import an address book.')
            ->addArgument('connection', InputArgument::REQUIRED, 'Which connection to use?')
            ->addArgument('user', InputArgument::REQUIRED, 'The user who own the addressbook.')
            ->addArgument('addressbook', InputArgument::REQUIRED, 'Which addressbook should be filled?')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Run in dry-run?')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDryRun = $input->getOption('dry-run', false);

        $io = new SymfonyStyle($input, $output);

        if ($isDryRun) {
            $io->comment('Dry run is activated. Nothing will be updated.');
        }

        $client = $this->clientFactory->getClientForConnection($input->getArgument('connection'));
        $client->setUser($input->getArgument('user'));
        $client->setAddressbook($input->getArgument('addressbook'));

        $io->text('Retrieving contacts from server, please wait...');

        $cards = $client->retrieveAllCards();

        $io->text('Find duplicates, please wait...');

        $duplicates = $this->duplicateFinder->findDuplicates($cards);

        foreach ($duplicates as $duplicate) {
            $cards = $duplicate->all();

            $mergedCard = $this->merger->merge($cards);

            $io->section('Duplicate: "'.(string) $mergedCard->FN.'"');

            $allValues = [];
            foreach ($mergedCard->children() as $child) {
                $name = $child->name;

                $line = [$name, (string) $child];
                foreach ($cards as $card) {
                    $line[] = (string) $card->{$name};
                }

                $allValues[] = $line;
            }

            $headers = ['Field', 'Merged contact'] + array_fill(0, count($cards), null);
            $io->table($headers, $allValues);

            if ( true === $io->confirm('Do you confirm the merge?', false)) {
                $client->updateContact($mergedCard);
                array_shift($cards);
                foreach ($cards as $card) {
                    $client->deleteContact($card);
                }
                $io->text('Contacts merged successfully!');
            } else {
                $io->text('Contacts not merged.');
            }
        }

        if ($isDryRun) {
            $io->success('All duplicates have been listed.');
        } else {
            $io->success('No more duplicates!');
        }
    }
}
