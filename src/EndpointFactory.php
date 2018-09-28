<?php declare(strict_types=1);

namespace Emarref\EventStore;

use Emarref\EventStore\Endpoint\Stream;

class EndpointFactory
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $stringName
     *
     * @return Stream
     */
    public function getStreamEndpoint(string $stringName): Stream
    {
        return new Stream($this->client, $stringName);
    }
}
