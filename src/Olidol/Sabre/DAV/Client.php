<?php

namespace Olidol\Sabre\DAV;

use Sabre\DAV\Client as BaseClient;
use Sabre\HTTP\ClientHttpException;
use Sabre\HTTP\Request;
use Sabre\VObject;

class Client extends BaseClient
{
    /**
     * retrieveAllCards
     *
     * @param string $user
     * @param string $addressbook
     *
     * @return []
     */
    public function retrieveAllCards($user, $addressbook)
    {
        $url = $this->getAbsoluteUrl("addressbooks/$user/$addressbook");

        try {
            $response = $this->send(new Request(
                'REPORT',
                $url,
                [
                    'Depth' => 1
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
                'exception' => $e
            ]);

            return [];
        }

        $xml = $response->getBody();

        $contacts = new \SimpleXMLElement($xml);
        $contacts->registerXPathNamespace('card', 'urn:ietf:params:xml:ns:carddav');

        $rawCards = $contacts->xpath('//card:address-data');

        $cards = [];
        foreach ($rawCards as $rawCard) {
            $cards[] = VObject\Reader::read((string) $rawCard);
        }

        return $cards;
    }
}
