<?php

namespace Olidol\Sabre\DAV;

use Sabre\DAV\Client as BaseClient;
use Sabre\HTTP\ClientHttpException;
use Sabre\HTTP\Request;
use Sabre\VObject;
use Sabre\VObject\Component\VCard;

class Client extends BaseClient
{
    protected $user;
    protected $addressbook;

    /**
     * retrieveAllCards.
     *
     * @return []
     */
    public function retrieveAllCards()
    {
        //$url = $this->getAbsoluteUrl("dav/addressbooks/user/$this->user/$this->addressbook");
        $url = $this->getAbsoluteUrl("addressbooks/$this->user/$this->addressbook");

        try {
            $response = $this->send(new Request(
                'REPORT',
                $url,
                [
                    'Content-Type' => 'text/xml',
                    'Depth' => 1,
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
                'exception' => $e,
            ]);

            return [];
        }

        $xml = $response->getBody();

        $contacts = new \SimpleXMLElement($xml);
        $contacts->registerXPathNamespace('card', 'urn:ietf:params:xml:ns:carddav');

        $rawCards = $contacts->xpath('//card:address-data');

        $cards = [];
        foreach ($rawCards as $rawCard) {
            $card = VObject\Reader::read((string) $rawCard);
            $cards[(string) $card->UID] = $card;
        }

        return $cards;
    }

    public function updateContact(VCard $card)
    {
        $uid = (string) $card->UID;
        $url = $this->getAbsoluteUrl("addressbooks/$this->user/$this->addressbook/$uid.vcf");

        try {
            $response = $this->send(new Request(
                'PUT',
                $url,
                [
                    'Content-Type' => 'text/vcard',
                    'charset' => 'utf-8',
                ],
                $card->serialize()
            ));
        } catch (ClientHttpException $e) {
            $this->logger->error('Unable to update contact.', [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function deleteContact(VCard $card)
    {
        $uid = (string) $card->UID;
        $url = $this->getAbsoluteUrl("addressbooks/$this->user/$this->addressbook/$uid.vcf");

        try {
            $response = $this->send(new Request(
                'DELETE',
                $url
            ));
        } catch (ClientHttpException $e) {
            $this->logger->error('Unable to delete contact.', [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setAddressbook($addressbook)
    {
        $this->addressbook = $addressbook;
    }
}
