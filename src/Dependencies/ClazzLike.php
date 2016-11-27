<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

abstract class ClazzLike implements Dependency
{
    /** @var string */
    private $name;

    /** @var Namespaze */
    private $namespaze;

    /**
     * @param string    $name
     * @param Namespaze $clazzNamespace
     */
    public function __construct(string $name, Namespaze $clazzNamespace = null)
    {
        $this->ensureClassNameIsValid($name);

        $this->name = $name;
        if ($clazzNamespace === null) {
            $clazzNamespace = new Namespaze([]);
        }
        $this->namespaze = $clazzNamespace;
    }

    public function equals(Dependency $other) : bool
    {
        return $this->toString() === $other->toString();
    }

    public function toString() : string
    {
        return $this->hasNamespace()
            ? $this->namespaze.'\\'.$this->name
            : $this->name;
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function namespaze() : Namespaze
    {
        return $this->namespaze;
    }

    public function hasNamespace() : bool
    {
        return $this->namespaze->toString() !== '';
    }

    public function count() : int
    {
        return 1 + $this->namespaze->count();
    }

    public function reduceToDepth(int $maxDepth) : Dependency
    {
        return $this->count() <= $maxDepth || $maxDepth === 0
            ? $this
            : $this->namespaze->reduceToDepth($maxDepth);
    }

    public function reduceDepthFromLeftBy(int $reduction) : Dependency
    {
        return $this->count() <= $reduction || $reduction === 0
            ? $this
            : new Clazz($this->name, $this->namespaze->reduceDepthFromLeftBy($reduction));
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @see http://php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class
     */
    private function ensureClassNameIsValid(string $name)
    {
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/u', $name) !== 1) {
            throw new \InvalidArgumentException('Class name "' . $name . '" is not valid.');
        }
    }

    public function inNamespaze(Namespaze $other) : bool
    {
        return $this->namespaze->inNamespaze($other);
    }
}
