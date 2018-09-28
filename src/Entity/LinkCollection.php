<?php declare(strict_types=1);

namespace Emarref\EventStore\Entity;

final class LinkCollection implements \IteratorAggregate
{
    public const RELATION_FIRST     = 'first';
    public const RELATION_LAST      = 'last';
    public const RELATION_NEXT      = 'next';
    public const RELATION_PREVIOUS  = 'previous';
    public const RELATION_ALTERNATE = 'alternate';
    public const RELATION_EDIT      = 'edit';

    /**
     * @var string
     */
    private $type;

    /**
     * @var Link[]
     */
    private $links;

    /**
     * @var array<string, int>
     */
    private $index;

    /**
     * @param string $type
     * @param Link   ...$links
     *
     * @return LinkCollection
     */
    public static function fromLinks(string $type, Link ...$links): self
    {
        return new static($type, ...$links);
    }

    /**
     * @return array
     */
    public function asPayload(): array
    {
        return \array_map(function (Link $link) {
            return $link->asPayload();
        }, $this->links);
    }

    /**
     * @return \ArrayIterator<Link>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->links);
    }

    /**
     * @param string $relation
     *
     * @return Link|null
     */
    public function getRelation(string $relation): ?Link
    {
        if (!$this->hasRelation($relation)) {
            return null;
        }

        return $this->links[$this->index[$relation]];
    }

    /**
     * @param string $relation
     */
    public function hasRelation(string $relation): bool
    {
        return \array_key_exists($relation, $this->index);
    }

    /**
     * @param string $type
     * @param Link   ...$links
     */
    private function __construct(string $type, Link ...$links)
    {
        $this->type  = $type;
        $this->setLinks(...$links);
    }

    /**
     * @param Link ...$links
     */
    private function setLinks(Link ...$links): void
    {
        $index = 0;

        foreach ($links as $link) {
            $this->index[$link->getRelation()] = $index;
            $this->links[$index] = $link;
            $index++;
        }
    }
}
