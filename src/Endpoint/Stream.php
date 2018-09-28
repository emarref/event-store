<?php declare(strict_types=1);

namespace Emarref\EventStore\Endpoint;

use Emarref\EventStore\Client;
use Emarref\EventStore\Entity;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Stream
{
    private const URI                = 'streams/%s';
    private const HEADER_HARD_DELETE = 'ES-HardDelete';
    private const HEADER_EVENT_TYPE  = 'ES-EventType';
    private const HEADER_EVENT_ID    = 'ES-EventId';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $uri;

    /**
     * @param Client $client
     * @param string $name
     */
    public function __construct(Client $client, string $name)
    {
        $this->client = $client;
        $this->name   = $name;
        $this->uri    = sprintf(self::URI, $name);
    }

    /**
     * @return Entity\Stream
     */
    public function read(): Entity\Stream
    {
        return $this->fromUri($this->uri);
    }

    /**
     * @param string        $type
     * @param array         $data
     * @param UuidInterface $id
     */
    public function writeEntry(string $type, array $data, UuidInterface $id = null): void
    {
        $this->writeEvent($this->uri, $type, $data, $id);
    }

    /**
     * @param string        $type
     * @param array         $data
     * @param UuidInterface $id
     */
    public function writeMetadata(string $type, array $data, UuidInterface $id = null): void
    {
        $this->writeEvent(sprintf('%s/%s', $this->uri, 'metadata'), $type, $data, $id);
    }

    /**
     * @return Entity\Event[]
     */
    public function readBackwards(): iterable
    {
        $originalStream = $this->read();

        if (!$originalStream->getLinks()->hasRelation(Entity\LinkCollection::RELATION_LAST)) {
            // All events are on this page.
            yield from $this->iterateEntries($originalStream->getEntries(), true);
        } else {
            $lastPage = $originalStream->getLinks()->getRelation(Entity\LinkCollection::RELATION_LAST);

            foreach ($this->iterateLinks($lastPage, true) as $stream) {
                yield from $this->iterateEntries($stream->getEntries(), true);
            }
        }
    }

    /**
     * @return Entity\Event[]
     */
    public function readForwards(): iterable
    {
        $stream = $this->read();

        if (!$stream->getLinks()->hasRelation(Entity\LinkCollection::RELATION_FIRST)) {
            return;
        }

        $firstPage = $stream->getLinks()->getRelation(Entity\LinkCollection::RELATION_FIRST);

        foreach ($this->iterateLinks($firstPage) as $stream) {
            yield from $this->iterateEntries($stream->getEntries(), false);
        }
    }

    /**
     * @param bool $hard
     */
    public function delete(bool $hard = false): void
    {
        $headers = [];

        if ($hard) {
            $headers[self::HEADER_HARD_DELETE] = 'true';
        }

        $this->client->delete($this->uri, $headers);
    }

    /**
     * @param int $eventNumber
     *
     * @return Entity\Event
     */
    public function readEvent(int $eventNumber): Entity\Event
    {
        $uri     = sprintf('%s/%d', $this->uri, $eventNumber);

        return Entity\Event::fromPayload($this->client->get($uri));
    }

    /**
     * @param Entity\Link $link
     * @param bool        $reverse
     *
     * @return Entity\Stream[]|iterable
     */
    private function iterateLinks(Entity\Link $link, $reverse = false): iterable
    {
        $iteratorPage = $reverse
            ? Entity\LinkCollection::RELATION_PREVIOUS
            : Entity\LinkCollection::RELATION_NEXT;

        do {
            $stream = $this->fromUri($link->getUri());
            yield $stream;
        } while ($link = $stream->getLinks()->getRelation($iteratorPage));
    }

    /**
     * @param Entity\EventCollection $events
     * @param bool                   $reverse
     *
     * @return iterable
     */
    private function iterateEntries(Entity\EventCollection $events, $reverse = false): iterable
    {
        $requests = function () use ($events): iterable {
            /** @var Entity\Event $event */
            foreach ($events as $event) {
                $link = $event->getLinks()
                    ->getRelation(Entity\LinkCollection::RELATION_ALTERNATE);

                if (null === $link) {
                    throw new \RuntimeException(sprintf(
                        'Unable to enrich event "%s" with no alternate link.',
                        $event->getId()
                    ));
                }

                yield new Request('get', $link->getUri());
            }
        };

        $readOrder = function (array $items) use ($reverse) {
            return $reverse ? \array_reverse($items) : $items;
        };

        foreach ($readOrder($this->client->pool($requests())) as $payload) {
            yield Entity\Event::fromPayload($payload);
        }
    }

    /**
     * @param string $uri
     *
     * @return Entity\Stream
     */
    private function fromUri(string $uri): Entity\Stream
    {
        $payload = $this->client->get($uri);

        return Entity\Stream::fromPayload($payload);
    }

    /**
     * @param string             $uri
     * @param string             $type
     * @param array              $data
     * @param UuidInterface|null $id
     *
     * @throws \Exception
     */
    private function writeEvent(string $uri, string $type, array $data, UuidInterface $id = null): void
    {
        $payload = [
            Entity\EventContent::PAYLOAD_EVENT_ID   => ($id ?? Uuid::uuid4())->toString(),
            Entity\EventContent::PAYLOAD_EVENT_TYPE => $type,
            Entity\EventContent::PAYLOAD_DATA       => $data,
        ];

        $this->writeEvents($uri, [$payload]);
    }

    /**
     * @param string $uri
     * @param array  $payload
     */
    private function writeEvents(string $uri, array $payload): void
    {
        $this->client->post($uri, $payload, [
            Client::HEADER_CONTENT_TYPE => Client::CONTENT_TYPE_EVENTS,
        ]);
    }
}
