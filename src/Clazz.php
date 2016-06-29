<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class Clazz
{
    const default = 0;
    const abstract = 1;
    const interface = 2;

    /** @var string */
    private $clazz;

    /** @var int */
    private $type;

    /**
     * @param string $clazz
     * @param int    $type
     */
    public function __construct(string $clazz, int $type = self::default)
    {
        $this->clazz = $clazz;
        $this->type = $type;
    }

    public function equals(Clazz $other) : bool
    {
        return $this->clazz === $other->clazz
            && $this->type === $other->type;
    }

    public function isAbstract() : bool
    {
        return $this->type === self::abstract;
    }

    public function isInterface() : bool
    {
        return $this->type === self::interface;
    }

    public function toString() : string
    {
        return $this->clazz;
    }

    public function __toString() : string
    {
        return $this->clazz;
    }

    public function hasNamespace() : bool
    {
        return strpos($this->clazz, '.') !== false;
    }
}
