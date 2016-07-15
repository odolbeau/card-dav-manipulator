<?php

namespace Olidol\DAV\Manipulator;

use Prophecy\Argument;
use Olidol\Sabre\ContactCleaner;
use Sabre\VObject;

class DuplicateFinderTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_find_duplicates_by_email()
    {
        $splitter = new VObject\Splitter\VCard(fopen(__DIR__.'/../../../fixtures/cards_with_duplicated_emails.vcf', 'r'));
        while (null !== $card = $splitter->getNext()) {
            $cards[] = $card;
        }

        $duplicateFinder = new DuplicateFinder();
        $duplicates = $duplicateFinder->findDuplicates($cards);

        $this->assertCount(3, $duplicates);

        foreach ($duplicates as $duplicate) {
            $cards = $duplicate->all();
            $this->assertTrue(1 < count($cards));
            $email = (string) array_shift($cards)->EMAIL;
            foreach ($cards as $card) {
                $this->assertSame((string) $card->EMAIL, $email);
            }
        }
    }
}
