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

class GenerateRandomCommand extends Command
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('address-book:generate-random')
            ->setDescription('Generate a random address book. For test purpose only.')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of contacts generated.', 100)
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'File to write to.', 'random_addressbook.vcf')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $faker = \Faker\Factory::create();

        $limit = $input->getOption('limit');
        $fp = fopen($input->getOption('output'), 'w');

        $progressBar = new ProgressBar($output, $limit);
        $progressBar->start();

        for ($i = 0; $i < $limit; $i++) {
            $card = new VObject\Component\VCard([
                'FN' => $faker->name,
                'BDAY' => $faker->date,
                'N' => [$faker->firstName, $faker->lastName],
            ]);

            $card->add('TEL', $faker->phoneNumber, $this->getType());
            $card->add('EMAIL', $faker->email, $this->getType());

            fwrite($fp, $card->serialize());
            $progressBar->advance();
        }

        $progressBar->finish();
        fclose($fp);

        $io->success("$limit contacts generated!");
    }

    /**
     * decorateWithType
     *
     * @return string
     */
    protected function getType()
    {
        switch (rand(0, 1) %3) {
            case 0:
                return [];
            case 1:
                return ['type' => 'home'];
            case 2:
                return ['type' => 'work'];
        }
    }
}
