<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class Clazz implements Dependency
{
    /** @var string */
    private $clazz;

    /** @var Namespaze */
    private $clazzNamespace;

    /**
     * @param string    $clazz
     * @param Namespaze $clazzNamespace
     */
    public function __construct(string $clazz, Namespaze $clazzNamespace = null)
    {
        $this->clazz = $clazz;
        if ($clazzNamespace === null) {
            $clazzNamespace = new Namespaze([]);
        }
        $this->clazzNamespace = $clazzNamespace;
    }

    public function equals(Dependency $other) : bool
    {
        return $this->toString() === $other->toString();
    }

    public function toString() : string
    {
        return $this->hasNamespace()
            ? $this->clazzNamespace.'\\'.$this->clazz
            : $this->clazz;
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function hasNamespace() : bool
    {
        return $this->clazzNamespace->toString() !== '';
    }

    public function depth() : int
    {
        return 1 + $this->clazzNamespace->depth();
    }

    public function reduceToDepth(int $maxDepth) : Dependency
    {
        return $this->depth() <= $maxDepth || $maxDepth === 0
            ? $this
            : $this->clazzNamespace->reduceToDepth($maxDepth);
    }
}
