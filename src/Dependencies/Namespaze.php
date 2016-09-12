<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Exceptions\IndexOutOfBoundsException;
use Mihaeu\PhpDependencies\Util\Util;

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

    /**
     * @param array $parts
     *
     * @throws \InvalidArgumentException
     */
    private function ensureNamespaceIsValid(array $parts)
    {
        if ($this->arrayContainsNotOnlyStrings($parts)) {
            throw new \InvalidArgumentException('Invalid namespace');
        }
    }

    public function count() : int
    {
        return count($this->parts);
    }

    public function namespaze() : Namespaze
    {
        return $this;
    }

    public function partByIndex(int $index) : Namespaze
    {
        if ($index < 0 || $index >= $this->count()) {
            throw new IndexOutOfBoundsException('Namespace index out of range.');
        }
        return new Namespaze([$this->parts[$index]]);
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

    public function toString() : string
    {
        return implode('\\', $this->parts);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function inNamespaze(Namespaze $other) : bool
    {
        return $other->toString() !== ''
            && $this->toString() !== ''
            && strpos($other->toString(), $this->toString()) === 0;
    }

    /**
     * @param array $parts
     *
     * @return bool
     */
    private function arrayContainsNotOnlyStrings(array $parts):bool
    {
        return Util::array_once($parts, function ($value) {
            return !is_string($value);
        });
    }
}
