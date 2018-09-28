<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

use Emarref\EventStore\Client;
use GuzzleHttp\Psr7\Request;

final class EventCollection implements \IteratorAggregate
{
    /**
     * @var Event[]
     */
    private $events;

    /**
     * @param Event  ...$events
     *
     * @return EventCollection
     */
    public static function fromEvents(Event ...$events): self
    {
        return new static(...$events);
    }

    /**
     * @return array
     */
    public function asPayload(): array
    {
        return \array_map(function (Event $event) {
            return $event->asPayload();
        }, $this->events);
    }

    /**
     * @return \ArrayIterator<Event>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->events);
    }

    /**
     * @param Client $client
     * @param bool   $reverse
     *
     * @return iterable
     */
    public function iterate(Client $client, bool $reverse = false): iterable
    {
        $requests = function (): iterable {
            foreach ($this->events as $event) {
                $link = $event->getLinks()->getRelation(LinkCollection::RELATION_ALTERNATE);

                if (null === $link) {
                    throw new \RuntimeException(sprintf('Unable to enrich event "%s" with no alternate link.', $event->getId()));
                }

                yield new Request('get', $link->getUri());
            }
        };

        $readOrder = function (array $items) use ($reverse) {
            return $reverse ? \array_reverse($items) : $items;
        };

        foreach ($readOrder($client->pool($requests())) as $payload) {
            yield Event::fromPayload($payload);
        }
    }

    /**
     * @param Event  ...$events
     */
    private function __construct(Event ...$events)
    {
        $this->events = $events;
    }
}
