<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\Util;

class Namespaze implements Dependency
{
    /** @var list<string> */
    private array $parts;

    /**
     * @throws \InvalidArgumentException
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

    public function count(): int
    {
        return count($this->parts);
    }

    public function namespaze(): Namespaze
    {
        return $this;
    }

    /**
     * @return string[]
     */
    public function parts(): array
    {
        return $this->parts;
    }

    public function reduceToDepth(int $maxDepth): Dependency
    {
        if ($maxDepth === 0 || $this->count() === $maxDepth) {
            return $this;
        }

        return $this->count() < $maxDepth
            ? new NullDependency()
            : new self(array_slice($this->parts, 0, $maxDepth));
    }

    public function reduceDepthFromLeftBy(int $reduction): Namespaze
    {
        return $reduction >= $this->count()
            ? new self([])
            : new self(array_slice($this->parts, $reduction));
    }

    public function equals(Dependency $other): bool
    {
        return $this->toString() === $other->toString();
    }

    public function toString(): string
    {
        return implode('\\', $this->parts);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function inNamespaze(Namespaze $other): bool
    {
        return $other->toString() !== ''
            && $this->toString() !== ''
            && strpos($this->toString(), $other->toString()) === 0;
    }

    /**
     * @param array $parts
     *
     * @return bool
     */
    private function arrayContainsNotOnlyStrings(array $parts): bool
    {
        return Util::array_once($parts, function ($value) {
            return !is_string($value);
        });
    }

    public function isNamespaced(): bool
    {
        return count($this->parts) > 0;
    }
}
