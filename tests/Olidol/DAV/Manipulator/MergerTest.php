<?php

namespace Olidol\DAV\Manipulator;

use Prophecy\Argument;
use Olidol\Sabre\ContactCleaner;
use Sabre\VObject;

class MergerTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_merge_and_clean()
    {
        $card1 = <<<EOC
BEGIN:VCARD
VERSION:3.0
FN:Firstname Lastname
BDAY:1986-03-20
BDAY:1986-03-20
N:NAME;Firstname;;;
N:NAME;Firstname;;;
N:NAME;Firstname;;;
UID:56f8ed99-8ae8-4d3d-aee0-34873861eae1
END:VCARD
EOC;

        $card2 = <<<EOC
BEGIN:VCARD
VERSION:3.0
FN:Long Firstname Lastname
BDAY:1986-03-20
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=HOME:0101010101
N:NAME;Long Firstname;;;
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=HOME,INTERNET:email@hotmail.fr
UID:56f8ed99-8ae8-4d3d-aee0-34873861eae2
END:VCARD
EOC;

        $expectedCard = <<<EOC
BEGIN:VCARD
VERSION:3.0
BDAY:1986-03-20
BDAY:1986-03-20
BDAY:1986-03-20
UID:56f8ed99-8ae8-4d3d-aee0-34873861eae1
FN:Long Firstname Lastname
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=HOME:0101010101
N:NAME;Long Firstname;;;
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=HOME,INTERNET:email@hotmail.fr
END:VCARD
EOC;

        $this->assertMerged($expectedCard, [$card1, $card2]);
    }

    /**
     * assertMerged
     *
     * @param string $expectedCard
     * @param [] $cardsToMerge
     */
    private function assertMerged($expectedCard, array $cardsToMerge)
    {
        // We transform all strings into VCards otherwise it's a mess to
        // compare the expectedResult (which usually doesn't respect line
        // endings) with the merged one.
        $cardsToMerge = array_map(function($v) {
            return VObject\Reader::read((string) $v);
        }, $cardsToMerge);
        $expectedCard = VObject\Reader::read((string) $expectedCard);

        $repairer = $this->prophesize('Olidol\DAV\Manipulator\Repairer');
        $repairer
            ->repair(Argument::Type('Sabre\VObject\Component\VCard'))
            ->shouldBeCalledTimes(1)
            ->willReturnArgument(0)
        ;

        $merger = new Merger($repairer->reveal());
        $mergedCard = $merger->merge($cardsToMerge);

        $this->assertSame($expectedCard->serialize(), $mergedCard->serialize());
    }
}
