<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

final class EventContent
{
    public const PAYLOAD_EVENT_STREAM_ID = 'eventStreamId';
    public const PAYLOAD_EVENT_NUMBER    = 'eventNumber';
    public const PAYLOAD_EVENT_TYPE      = 'eventType';
    public const PAYLOAD_EVENT_ID        = 'eventId';
    public const PAYLOAD_DATA            = 'data';
    public const PAYLOAD_METADATA        = 'metadata';

    /**
     * @var string
     */
    private $eventStreamId;

    /**
     * @var int
     */
    private $eventNumber;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string
     */
    private $eventId;

    /**
     * @var object
     */
    private $data;

    /**
     * @var object
     */
    private $metadata;

    /**
     * @return EventContent
     */
    public static function fromPayload(array $payload): self
    {
        $self                = new static();
        $self->eventStreamId = $payload[self::PAYLOAD_EVENT_STREAM_ID];
        $self->eventNumber   = (int) $payload[self::PAYLOAD_EVENT_NUMBER];
        $self->eventType     = $payload[self::PAYLOAD_EVENT_TYPE];
        $self->eventId       = $payload[self::PAYLOAD_EVENT_ID];
        $self->data          = (object) $payload[self::PAYLOAD_DATA];
        $self->metadata      = (object) $payload[self::PAYLOAD_METADATA];

        return $self;
    }

    /**
     * @param Event $eventContent
     *
     * @return array
     */
    public static function toPayload(EventContent $eventContent): array
    {
        return \array_filter([
            self::PAYLOAD_EVENT_STREAM_ID => $eventContent->getEventStreamId(),
            self::PAYLOAD_EVENT_NUMBER    => $eventContent->getEventNumber(),
            self::PAYLOAD_EVENT_TYPE      => $eventContent->getEventType(),
            self::PAYLOAD_EVENT_ID        => $eventContent->getEventId(),
            self::PAYLOAD_DATA            => (array) $eventContent->getData(),
            self::PAYLOAD_METADATA        => (array) $eventContent->getMetadata(),
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
    public function getEventStreamId(): string
    {
        return $this->eventStreamId;
    }

    /**
     * @return int
     */
    public function getEventNumber(): int
    {
        return $this->eventNumber;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @return string
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * @return object
     */
    public function getData(): object
    {
        return $this->data;
    }

    /**
     * @return object
     */
    public function getMetadata(): object
    {
        return $this->metadata;
    }

    private function __construct()
    {
    }
}
