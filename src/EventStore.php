<?php declare(strict_types=1);

namespace Emarref\EventStore;

use Emarref\EventStore\Endpoint\Stream;

class EventStore
{
    public const DATE_FORMAT = 'Y-m-d\TH:i:s.u\Z';

    /**
     * @var EndpointFactory
     */
    private $endpointFactory;

    /**
     * @param EndpointFactory $endpointFactory
     */
    public function __construct(EndpointFactory $endpointFactory)
    {
        $this->endpointFactory = $endpointFactory;
    }

    /**
     * @param Client $client
     *
     * @return EventStore
     */
    public static function fromClient(Client $client): self
    {
        return new static(new EndpointFactory($client));
    }

    /**
     * @param string $streamName
     *
     * @return Stream
     */
    public function getStream(string $streamName): Stream
    {
        return $this->endpointFactory->getStreamEndpoint($streamName);
    }
}
