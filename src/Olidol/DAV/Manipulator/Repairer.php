<?php

namespace Olidol\DAV\Manipulator;

use Sabre\VObject\Component\VCard;
use Olidol\DAV\Manipulator\Exception\DifferentValuesForUniqPropertyException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Repairer
{
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * repair
     *
     * @param VCard $contact
     *
     * @return VCard
     */
    public function repair($contact)
    {
        foreach (['ANNIVERSARY', 'BDAY', 'GENDER', 'KIND', 'N', 'PRODID', 'REV'] as $property) {
            $values = $contact->select($property);
            $count = count($values);

            if (1 >= $count) {
                continue;
            }

            $this->logger->info('Found more than 1 value ({count}) for property "{property}"', [
                'property' => $property,
                'count' => $count,
            ]);

            $stringValues = [];
            foreach ($values as $value) {
                $stringValues[] = $value->getValue();
            }

            $stringValues = array_values(array_unique($stringValues));

            if (1 !== count($stringValues)) {
                throw new DifferentValuesForUniqPropertyException($name, $stringValues);
            }

            // All values are similar, just overwrite the exist one with the actual default one.
            $contact->$property = $contact->$property;
        }

        return $contact;
    }
}
