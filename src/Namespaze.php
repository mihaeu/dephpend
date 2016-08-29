<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class Namespaze implements Dependency
{
    /** @var string[] */
    private $parts;

    /**
     * @param \String[] $parts
     */
    public function __construct(array $parts)
    {
        $this->ensureNamespaceIsValid($parts);
        $this->parts = $parts;
    }

    public function toString() : string
    {
        return implode('\\', $this->parts);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     * @param array $parts
     */
    private function ensureNamespaceIsValid(array $parts)
    {
        if (Util::array_once($parts, function ($value, $index) {
            return !is_string($value);
        })
        ) {
            throw new \InvalidArgumentException('Invalid namespace');
        }
    }

    public function count() : int
    {
        return count($this->parts);
    }

    public function reduceToDepth(int $maxDepth) : Dependency
    {
        return $this->count() <= $maxDepth || $maxDepth === 0
            ? $this
            : new self(array_slice($this->parts, 0, $maxDepth));
    }

    public function reduceDepthFromLeftBy(int $reduction) : Dependency
    {
        return $reduction >= $this->count()
            ? new self([])
            : new self(array_slice($this->parts, $reduction));
    }

    public function equals(Dependency $other) : bool
    {
        return $this->toString() === $other->toString();
    }
}
