<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class ClassDependencies implements \Countable
{
    /** @var Clazz */
    private $clazz;

    /** @var Clazz[] */
    private $dependencies;

    /**
     * @param Clazz $clazz
     */
    public function __construct(Clazz $clazz)
    {
        $this->clazz = $clazz;
    }

    public function addDependency(Clazz $class)
    {
        $this->dependencies[] = $class;
    }

    /**
     * @return Clazz
     */
    public function clazz() : Clazz
    {
        return $this->clazz;
    }

    /**
     * @return Clazz[]
     */
    public function dependencies() : array
    {
        return $this->dependencies;
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
    public function count() : int
    {
        return count($this->dependencies());
    }
}
