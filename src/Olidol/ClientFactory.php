<?php

namespace Olidol;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Olidol\Sabre\DAV\Client;

class ClientFactory
{
    protected $config;
    protected $logger;

    protected $clients = [];

    /**
     * __construct.
     *
     * @param Config          $config
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * getClientForConnection.
     *
     * @param string $name
     *
     * @return Client
     */
    public function getClientForConnection($name)
    {
        if (array_key_exists($name, $this->clients)) {
            return $this->clients[$name];
        }

        $connections = $this->config->load('connections');

        if (!array_key_exists($name, $connections)) {
            throw new \InvalidArgumentException("Unknown connection $name");
        }

        $client = new Client($connections[$name]);

        $this->clients[$name] = $client;

        return $client;
    }
}
