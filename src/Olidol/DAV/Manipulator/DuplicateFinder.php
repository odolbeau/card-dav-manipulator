<?php

namespace Olidol\DAV\Manipulator;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Olidol\DAV\Manipulator\Model\Duplicate;

class DuplicateFinder
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * findDuplicates.
     *
     * @param array $cards
     *
     * @return []
     */
    public function findDuplicates(array $cards)
    {
        $cardsEmails = [];
        foreach ($cards as $id => $card) {
            $emails = $card->EMAIL;
            if (0 === count($emails)) {
                continue;
            }

            foreach ($emails as $email) {
                $email = $email->getParts()[0];
                if (!array_key_exists($email, $cardsEmails)) {
                    $cardsEmails[$email] = [];
                }

                $cardsEmails[$email][] = $id;
            }
        }

        $cardsEmails = array_filter($cardsEmails, function ($v) {
            return 1 < count(array_unique($v));
        });

        $duplicates = [];
        foreach ($cardsEmails as $email => $cardsUid) {
            $duplicate = new Duplicate();

            foreach ($cardsUid as $cardUid) {
                $duplicate->add($cards[$cardUid]);
            }

            $duplicates[] = $duplicate;
        }

        return $duplicates;
    }
}
