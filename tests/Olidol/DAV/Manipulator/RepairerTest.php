<?php

namespace Olidol\DAV\Manipulator;

use Prophecy\Argument;
use Olidol\Sabre\ContactCleaner;
use Sabre\VObject;

class RepairerTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_removes_duplicated_uniq_equals_properties()
    {
        $cardToClean = <<<EOC
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
        $expectedCard = <<<EOC
BEGIN:VCARD
VERSION:3.0
FN:Firstname Lastname
UID:56f8ed99-8ae8-4d3d-aee0-34873861eae1
BDAY:1986-03-20
N:NAME;Firstname;;;
END:VCARD
EOC;
        $this->assertCleaned($cardToClean, $expectedCard);
    }

    public function test_it_removes_duplicated_phones_and_emails()
    {
        $cardToClean = <<<EOC
BEGIN:VCARD
VERSION:3.0
FN:Firstname Lastname
BDAY:1986-03-20
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=HOME:0101010101
N:NAME;Firstname;;;
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=HOME,INTERNET:email@hotmail.fr
UID:56f8ed99-8ae8-4d3d-aee0-34873861eae1
END:VCARD
EOC;

        $expectedCard = <<<EOC
BEGIN:VCARD
VERSION:3.0
FN:Firstname Lastname
BDAY:1986-03-20
N:NAME;Firstname;;;
UID:56f8ed99-8ae8-4d3d-aee0-34873861eae1
TEL;TYPE=VOICE,CELL:0606060606
TEL;TYPE=HOME:0101010101
EMAIL;TYPE=INTERNET:email@hotmail.fr
EMAIL;TYPE=HOME,INTERNET:email@hotmail.fr
END:VCARD
EOC;

        $this->assertCleaned($cardToClean, $expectedCard);
    }

    /**
     * assertCleaned
     *
     * @param string $cardToClean
     * @param string $expectedCard
     */
    private function assertCleaned($cardToClean, $expectedCard)
    {
        // We transform both strings into VCards otherwise it's a mess to
        // compare the expectedResult which usually doesn't respect line
        // endings.
        $card = VObject\Reader::read((string) $cardToClean);
        $expectedCard = VObject\Reader::read((string) $expectedCard);

        $contactCleaner = new Repairer();
        $cleanedCard = $contactCleaner->repair($card);

        $this->assertSame($expectedCard->serialize(), $cleanedCard->serialize());
    }
}
