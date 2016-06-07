<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class Dependency
{
    /** @var Clazz */
    private $from;

    /** @var Clazz */
    private $to;

    /**
     * @param Clazz $from
     * @param Clazz $to
     */
    public function __construct(Clazz $from, Clazz $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return Clazz
     */
    public function from() : Clazz
    {
        return $this->from;
    }

    /**
     * @return Clazz
     */
    public function to() : Clazz
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->from.' --> '.$this->to;
    }
}
