<?php

namespace Olidol\DAV\Manipulator;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Merger
{
    protected $repairer;
    protected $logger;

    public function __construct(Repairer $repairer, LoggerInterface $logger = null)
    {
        $this->repairer = $repairer;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * merge.
     *
     * @param array $contacts
     *
     * @return VCard
     */
    public function merge(array $contacts)
    {
        // We need to clone the first contact, otherwise it will be duplicated.
        $mainContact = clone array_shift($contacts);

        // Those values are heavily used, avoid to retrieve them all the time
        $mainEmail = 0 < count($mainContact->select('EMAIL')) ? (string) $mainContact->select('EMAIL')[0] : null;
        $mainNParts = count($mainContact->N->getParts());

        // Remove irrelevant values
        if ($mainEmail === (string) $mainContact->select('FN')[0]) {
            unset($mainContact->FN);
        }

        foreach ($contacts as $contact) {
            foreach ($contact->children() as $property) {
                $value = $property->getValue();

                switch ($property->name) {
                    // Keep first contact values for those properties.
                    case 'UID':
                    case 'VERSION':
                    case 'REV':
                        continue;
                    // For those properties, the longest value may be the best one.
                    case 'N':
                        if (count($property->getParts()) > $mainNParts) {
                            $mainContact->add($property);
                            break;
                        }
                    case 'FN':
                        // Directly keep this value if the main contact is empty.
                        if (0 === count($mainContact->select($property->name))) {
                            $mainContact->add($property);
                            break;
                        }

                        // If FN or N is the same as the EMAIL, it's not relevant to keep it.
                        if ((string) $value === $mainEmail) {
                            break;
                        }

                        // If the current value is shorter than the existing one, we continue to the next property.
                        if (strlen($value) <= strlen($mainContact->select($property->name)[0])) {
                            break;
                        }
                        unset($mainContact->{$property->name});
                        $mainContact->add($property);

                        break;

                    default:
                        $mainContact->add($property);
                }
            }
        }

        $this->repairer->repair($mainContact);

        return $mainContact;
    }
}
