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
     * merge
     *
     * @param array $contacts
     *
     * @return VCard
     */
    public function merge(array $contacts)
    {
        $mainContact = array_shift($contacts);

        foreach ($contacts as $contact) {
            foreach ($contact->children() as $property) {
                switch ($property->name) {
                    // Keep first contact values for those properties.
                    case 'UID':
                    case 'VERSION':
                        continue;
                    // For those properties, the longest value may be the best one.
                    case 'N':
                    case 'FN':
                        // If the current value is shorter than the existing one, we continue to the next property.
                        if (strlen($property->getValue()) <= strlen($mainContact->select($property->name)[0])) {
                            break;
                        }
                        unset($mainContact->{$property->name});

                    default:
                        $mainContact->add($property);
                }
            }
        }

        $this->repairer->repair($mainContact);

        return $mainContact;
    }
}
