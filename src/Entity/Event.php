<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

use Emarref\EventStore\EventStore;

final class Event
{
    public const  PAYLOAD_TITLE   = 'title';
    public const  PAYLOAD_ID      = 'id';
    public const  PAYLOAD_UPDATED = 'updated';
    public const  PAYLOAD_AUTHOR  = 'author';
    public const  PAYLOAD_SUMMARY = 'summary';
    public const  PAYLOAD_CONTENT = 'content';
    public const  PAYLOAD_LINKS   = 'links';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $updated;

    /**
     * @var Author
     */
    private $author;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var EventContent|null
     */
    private $content;

    /**
     * @var LinkCollection
     */
    private $links;

    /**
     * @param array $payload
     *
     * @return Event
     */
    public static function fromPayload(array $payload): self
    {
        $self          = new static();
        $self->title   = $payload[self::PAYLOAD_TITLE];
        $self->id      = $payload[self::PAYLOAD_ID];
        $self->updated = \DateTimeImmutable::createFromFormat(EventStore::DATE_FORMAT, $payload[self::PAYLOAD_UPDATED]);
        $self->author  = Author::fromPayload($payload[self::PAYLOAD_AUTHOR]);
        $self->summary = $payload[self::PAYLOAD_SUMMARY];
        $self->content = !empty($payload[self::PAYLOAD_CONTENT])
            ? EventContent::fromPayload($payload[self::PAYLOAD_CONTENT])
            : null;
        $self->links   = LinkCollection::fromLinks(self::class, ...\array_map([Link::class, 'fromPayload'], $payload[self::PAYLOAD_LINKS]));

        return $self;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public static function toPayload(Event $event): array
    {
        return \array_filter([
            self::PAYLOAD_TITLE   => $event->getTitle(),
            self::PAYLOAD_ID      => $event->getId(),
            self::PAYLOAD_UPDATED => $event->getUpdated()->format(EventStore::DATE_FORMAT),
            self::PAYLOAD_AUTHOR  => $event->getAuthor()->asPayload(),
            self::PAYLOAD_SUMMARY => $event->getSummary(),
            self::PAYLOAD_CONTENT => $event->getContent() ? $event->getContent()->asPayload() : null,
            self::PAYLOAD_LINKS   => $event->getLinks()->asPayload(),
        ], function ($value) {
            return $value !== null;
        });
    }

    /**
     * @return array
     */
    public function asPayload(): array
    {
        return static::toPayload($this);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdated(): \DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * @return Author
     */
    public function getAuthor(): Author
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @return LinkCollection
     */
    public function getLinks(): LinkCollection
    {
        return $this->links;
    }

    /**
     * @return EventContent|null
     */
    public function getContent(): ?EventContent
    {
        return $this->content;
    }

    private function __construct()
    {
    }
}
