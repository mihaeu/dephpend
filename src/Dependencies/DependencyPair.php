<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class DependencyPair
{
    /** @var Dependency */
    private $from;

    /** @var Dependency */
    private $to;

    /**
     * @param Dependency $from
     * @param Dependency $to
     */
    public function __construct(Dependency $from, Dependency $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return Dependency
     */
    public function from() : Dependency
    {
        return $this->from;
    }

    /**
     * @return Dependency
     */
    public function to() : Dependency
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->from->toString().' --> '.$this->to->toString();
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
}
