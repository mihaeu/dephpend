<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\Util;

class Namespaze implements Dependency
{
    /**
     * @param list<string> $parts
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(private array $parts)
    {
        $this->ensureNamespaceIsValid($parts);
    }

    /**
     * @param array<string> $parts
     *
     * @throws \InvalidArgumentException
     */
    private function ensureNamespaceIsValid(array $parts): void
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
     * @return list<string>
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
     * @param array<string> $parts
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
