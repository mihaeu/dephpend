<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class Clazz
{
    /** @var string */
    private $clazz;

    /**
     * @param string $clazz
     */
    public function __construct(string $clazz)
    {
        $this->clazz = $clazz;
    }

    public function toString() : string
    {
        return $this->clazz;
    }

    public function __toString() : string
    {
        return $this->clazz;
    }
}
