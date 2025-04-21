<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class NullDependency implements Dependency
{
    public function reduceToDepth(int $maxDepth): Dependency
    {
        return new NullDependency();
    }

    public function reduceDepthFromLeftBy(int $reduction): Dependency
    {
        return new NullDependency();
    }

    public function equals(Dependency $other): bool
    {
        return false;
    }

    public function toString(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    public function namespaze(): Namespaze
    {
        return new Namespaze([]);
    }

    public function inNamespaze(Namespaze $other): bool
    {
        return false;
    }

    public function count(): int
    {
        return 0;
    }

    public function isNamespaced(): bool
    {
        return false;
    }
}
