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

class ExportCommand extends Command
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
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('address-book:export')
            ->setDescription('Export an address book.')
            ->addArgument('connection', InputArgument::REQUIRED, 'Which connection to use?')
            ->addArgument('user', InputArgument::REQUIRED, 'The user who own the addressbook to export.')
            ->addArgument('addressbook', InputArgument::REQUIRED, 'Which addressbook should be exported?')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'File to write to.', 'output.vcf')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $client = $this->clientFactory->getClientForConnection($input->getArgument('connection'));

        $user = $input->getArgument('user');
        $addressbook = $input->getArgument('addressbook');

        $url = $client->getAbsoluteUrl("addressbooks/$user/$addressbook");

        $io->text("Retrieving contacts from $url, please wait...");

        try {
            $response = $client->send(new Request(
                'REPORT',
                $url,
                [
                    'Depth' => 1
                ],
                '<card:addressbook-query xmlns:d="DAV:" xmlns:card="urn:ietf:params:xml:ns:carddav">
                    <d:prop>
                        <d:getetag />
                        <card:address-data />
                    </d:prop>
                </card:addressbook-query>'
            ));
        } catch (ClientHttpException $e) {
            $this->logger->error('Unable to propfind contacts.', [
                'exception' => $e
            ]);

            throw $e;
        }

        $io->text("Write contacts to file.");

        $xml = $response->getBody();

        $fp = fopen($input->getOption('output'), 'w');

        $contacts = new \SimpleXMLElement($xml);
        $contacts->registerXPathNamespace('card', 'urn:ietf:params:xml:ns:carddav');

        $cards = $contacts->xpath('//card:address-data');

        foreach ($cards as $card) {
            fwrite($fp, $card);
        }

        $io->success(count($cards).' contacts exported!');
    }
}
