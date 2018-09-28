<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

final class Link
{
    private const PAYLOAD_URI      = 'uri';
    private const PAYLOAD_RELATION = 'relation';

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $relation;

    /**
     * @param array $payload
     *
     * @return Link
     */
    public static function fromPayload(array $payload): self
    {
        return (new static())
            ->setUri($payload[self::PAYLOAD_URI])
            ->setRelation($payload[self::PAYLOAD_RELATION]);
    }

    /**
     * @param Event $link
     *
     * @return array
     */
    public static function toPayload(Link $link): array
    {
        return \array_filter([
            self::PAYLOAD_URI      => $link->getUri(),
            self::PAYLOAD_RELATION => $link->getRelation(),
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
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @return Link
     */
    private function setUri(string $uri): Link
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @param string $relation
     *
     * @return Link
     */
    private function setRelation(string $relation): Link
    {
        $this->relation = $relation;

        return $this;
    }

    private function __construct()
    {
    }
}
