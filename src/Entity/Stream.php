<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

use Emarref\EventStore\EventStore;

final class Stream
{
    private const PAYLOAD_TITLE          = 'title';
    private const PAYLOAD_ID             = 'id';
    private const PAYLOAD_UPDATED        = 'updated';
    private const PAYLOAD_STREAM_ID      = 'streamId';
    private const PAYLOAD_AUTHOR         = 'author';
    private const PAYLOAD_HEAD_OF_STREAM = 'headOfStream';
    private const PAYLOAD_SELF_URL       = 'selfUrl';
    private const PAYLOAD_E_TAG          = 'eTag';
    private const PAYLOAD_LINKS          = 'links';
    private const PAYLOAD_ENTRIES        = 'entries';

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
     * @var string
     */
    private $streamId;

    /**
     * @var Author
     */
    private $author;

    /**
     * @var bool
     */
    private $headOfStream;

    /**
     * @var string|null
     */
    private $selfUrl;

    /**
     * @var string|null
     */
    private $eTag;

    /**
     * @var LinkCollection
     */
    private $links;

    /**
     * @var EventCollection
     */
    private $entries;

    /**
     * @param array $payload
     *
     * @return Stream
     */
    public static function fromPayload(array $payload): self
    {
        $self               = new static();
        $self->title        = $payload[self::PAYLOAD_TITLE];
        $self->id           = $payload[self::PAYLOAD_ID];
        $self->updated      = \DateTimeImmutable::createFromFormat(EventStore::DATE_FORMAT, $payload[self::PAYLOAD_UPDATED]);
        $self->streamId     = $payload[self::PAYLOAD_STREAM_ID];
        $self->author       = Author::fromPayload($payload[self::PAYLOAD_AUTHOR]);
        $self->headOfStream = (bool) $payload[self::PAYLOAD_HEAD_OF_STREAM];
        $self->selfUrl      = $payload[self::PAYLOAD_SELF_URL] ?? null;
        $self->eTag         = $payload[self::PAYLOAD_E_TAG] ?? null;
        $self->links        = LinkCollection::fromLinks(static::class, ...\array_map([Link::class, 'fromPayload'], $payload[self::PAYLOAD_LINKS]));
        $self->entries      = EventCollection::fromEvents(...\array_map([Event::class, 'fromPayload'], $payload[self::PAYLOAD_ENTRIES]));

        return $self;
    }

    /**
     * @param Stream $stream
     *
     * @return array
     */
    public static function toPayload(Stream $stream): array
    {
        return \array_filter([
            self::PAYLOAD_TITLE          => $stream->getTitle(),
            self::PAYLOAD_ID             => $stream->getId(),
            self::PAYLOAD_UPDATED        => $stream->getUpdated()->format(EventStore::DATE_FORMAT),
            self::PAYLOAD_STREAM_ID      => $stream->getStreamId(),
            self::PAYLOAD_AUTHOR         => $stream->getAuthor()->asPayload(),
            self::PAYLOAD_HEAD_OF_STREAM => $stream->isHeadOfStream(),
            self::PAYLOAD_SELF_URL       => $stream->getSelfUrl(),
            self::PAYLOAD_E_TAG          => $stream->getETag(),
            self::PAYLOAD_LINKS          => $stream->getLinks()->asPayload(),
            self::PAYLOAD_ENTRIES        => $stream->getEntries()->asPayload(),
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
     * @return string
     */
    public function getStreamId(): string
    {
        return $this->streamId;
    }

    /**
     * @return Author
     */
    public function getAuthor(): Author
    {
        return $this->author;
    }

    /**
     * @return bool
     */
    public function isHeadOfStream(): bool
    {
        return $this->headOfStream;
    }

    /**
     * @return string|null
     */
    public function getSelfUrl(): ?string
    {
        return $this->selfUrl;
    }

    /**
     * @return string|null
     */
    public function getETag(): ?string
    {
        return $this->eTag;
    }

    /**
     * @return LinkCollection
     */
    public function getLinks(): LinkCollection
    {
        return $this->links;
    }

    /**
     * @return EventCollection
     */
    public function getEntries(): EventCollection
    {
        return $this->entries;
    }

    private function __construct()
    {
    }
}
