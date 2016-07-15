<?php

namespace Olidol\DAV\Manipulator\Model;

use Sabre\VObject\Component\VCard;

class Duplicate
{
    protected $cards = [];

    public function __construct(VCard $card = null)
    {
        if (null !== $card) {
            $this->add($card);
        }
    }

    /**
     * add
     *
     * @param VCard $card
     */
    public function add(VCard $card)
    {
        $this->cards[(string) $card->UID] = $card;
    }

    /**
     * all
     *
     * @return []
     */
    public function all()
    {
        return $this->cards;
    }
}
