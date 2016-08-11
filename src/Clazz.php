<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class Clazz implements Dependency
{
    /** @var string */
    private $clazz;

    /** @var ClazzNamespace */
    private $clazzNamespace;

    /**
     * @param string         $clazz
     * @param ClazzNamespace $clazzNamespace
     */
    public function __construct(string $clazz, ClazzNamespace $clazzNamespace = null)
    {
        $this->clazz = $clazz;
        if ($clazzNamespace === null) {
            $clazzNamespace = new ClazzNamespace([]);
        }
        $this->clazzNamespace = $clazzNamespace;
    }

    public function equals(Clazz $other) : bool
    {
        return $this->clazz === $other->clazz
            && $this->clazzNamespace->toString() === $other->clazzNamespace->toString();
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
}
