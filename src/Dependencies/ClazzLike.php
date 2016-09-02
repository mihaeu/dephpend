<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

abstract class ClazzLike implements Dependency
{
    /** @var string */
    private $name;

    /** @var Namespaze */
    private $clazzNamespace;

    /**
     * @param string    $name
     * @param Namespaze $clazzNamespace
     */
    public function __construct(string $name, Namespaze $clazzNamespace = null)
    {
        $this->name = $name;
        if ($clazzNamespace === null) {
            $clazzNamespace = new Namespaze([]);
        }
        $this->clazzNamespace = $clazzNamespace;
    }

    public function equals(Dependency $other) : bool
    {
        return $this->toString() === $other->toString()
            && $this instanceof $other;
    }

    public function toString() : string
    {
        return $this->hasNamespace()
            ? $this->clazzNamespace.'\\'.$this->name
            : $this->name;
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function hasNamespace() : bool
    {
        return $this->clazzNamespace->toString() !== '';
    }

    public function count() : int
    {
        return 1 + $this->clazzNamespace->count();
    }

    public function reduceToDepth(int $maxDepth) : Dependency
    {
        return $this->count() <= $maxDepth || $maxDepth === 0
            ? $this
            : $this->clazzNamespace->reduceToDepth($maxDepth);
    }

    public function reduceDepthFromLeftBy(int $reduction) : Dependency
    {
        return $this->count() <= $reduction || $reduction === 0
            ? $this
            : new Clazz($this->name, $this->clazzNamespace->reduceDepthFromLeftBy($reduction));
    }
}
