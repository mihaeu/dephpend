<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class DependencyPair
{
    /** @var Dependency */
    private $from;

    /** @var DependencySet */
    private $to;

    /**
     * @param Dependency $from
     * @param DependencySet $to
     */
    public function __construct(Dependency $from, DependencySet $to = null)
    {
        $this->from = $from;
        $this->to = $to !== null
            ? $to
            : new DependencySet();
        $this->removeDependencyOnItself();
    }

    /**
     * @return Dependency
     */
    public function from() : Dependency
    {
        return $this->from;
    }

    /**
     * @return DependencySet
     */
    public function to() : DependencySet
    {
        return $this->to;
    }

    public function addDependency(Dependency $dependency) : DependencyPair
    {
        $clone = clone $this;
        if ($this->from->equals($dependency)) {
            return $clone;
        }
        $clone->to = $this->to->add($dependency);
        return $clone;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->to->reduce('', function (string $total, Dependency $dependency) {
            return $total.($total === '' ? '' : PHP_EOL).$this->from->toString().' --> '.$dependency->toString();
        });
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }

    private function removeDependencyOnItself()
    {
        $this->to = $this->to->filter(function (Dependency $dependency) {
            return !$dependency->equals($this->from);
        });
    }
}
