<?php

namespace Olidol\Sabre\DAV;

use Sabre\DAV\Client as BaseClient;
use Sabre\HTTP\ClientHttpException;
use Sabre\HTTP\Request;
use Sabre\VObject;
use Sabre\VObject\Component\VCard;

class Client
{
    protected $client;
    protected $type;

    protected $user;
    protected $addressbook;

    /**
     * __construct
     *
     * @param string $type
     * @param string $baseUri
     * @param string $userName
     * @param string $password
     */
    public function __construct($type, $baseUri, $userName, $password)
    {
        $this->client = new BaseClient([
            'baseUri' => $baseUri,
            'userName' => $userName,
            'password' => $password,
        ]);

        $this->type = $type;
    }

    /**
     * retrieveAllCards.
     *
     * @return []
     */
    public function retrieveAllCards()
    {
        try {
            $response = $this->client->send(new Request(
                'REPORT',
                $this->getAddressbookAbsoluteUrl(),
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

    /**
     * updateContact
     *
     * @param VCard $card
     */
    public function updateContact(VCard $card)
    {
        $uid = (string) $card->UID;

        try {
            $this->client->send(new Request(
                'PUT',
                $this->getContactAbsoluteUrl($uid),
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

    /**
     * deleteContact
     *
     * @param VCard $card
     */
    public function deleteContact(VCard $card)
    {
        $uid = (string) $card->UID;

        try {
            $response = $this->client->send(new Request(
                'DELETE',
                $this->getContactAbsoluteUrl($uid)
            ));
        } catch (ClientHttpException $e) {
            $this->logger->error('Unable to delete contact.', [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * setUser
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * setAddressbook
     *
     * @param string $addressbook
     */
    public function setAddressbook($addressbook)
    {
        $this->addressbook = $addressbook;
    }

    /**
     * getAddressbookAbsoluteUrl
     *
     * @return string
     */
    protected function getAddressbookAbsoluteUrl()
    {
        switch ($this->type) {
            case 'baikal':
                return $this->client->getAbsoluteUrl("addressbooks/$this->user/$this->addressbook");
            case 'fastmail':
                return $this->client->getAbsoluteUrl("addressbooks/user/$this->user/$this->addressbook");
        }

        throw new UnknownTypeException($this->type);
    }

    /**
     * getContactAbsoluteUrl
     *
     * @return string
     */
    protected function getContactAbsoluteUrl($uid)
    {
        switch ($this->type) {
            case 'baikal':
                return $this->client->getAbsoluteUrl("addressbooks/$this->user/$this->addressbook/$uid.vcf");
            case 'fastmail':
                return $this->client->getAbsoluteUrl("addressbooks/user/$this->user/$this->addressbook/$uid.vcf");
        }

        throw new UnknownTypeException($this->type);
    }
}
