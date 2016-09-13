<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class NullDependency implements Dependency
{
    public function reduceToDepth(int $maxDepth) : Dependency
    {
        return new NullDependency();
    }

    public function reduceDepthFromLeftBy(int $reduction) : Dependency
    {
        return new NullDependency();
    }

    public function equals(Dependency $other) : bool
    {
        return false;
    }

    public function toString() : string
    {
        return '';
    }

    public function namespaze() : Namespaze
    {
        return new Namespaze([]);
    }

    public function inNamespaze(Namespaze $other) : bool
    {
        return false;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return 0;
    }
}
