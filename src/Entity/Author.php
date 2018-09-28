<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

final class Author
{
    private const PAYLOAD_NAME = 'name';

    /**
     * @var string
     */
    private $name;

    /**
     * @param array $payload
     *
     * @return Author
     */
    public static function fromPayload(array $payload): self
    {
        return (new static())
            ->setName($payload[self::PAYLOAD_NAME]);
    }

    /**
     * @param Author $author
     *
     * @return array
     */
    public static function toPayload(Author $author): array
    {
        return \array_filter([
            self::PAYLOAD_NAME => $author->getName(),
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Author
     */
    private function setName(string $name): Author
    {
        $this->name = $name;

        return $this;
    }

    private function __construct()
    {
    }
}
