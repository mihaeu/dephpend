<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class ClazzNamespace
{
    /** @var string[] */
    private $parts;

    /**
     * @param \String[] $parts
     */
    public function __construct(array $parts)
    {
        $this->ensureNamespaceIsValid($parts);
        $this->parts = $parts;
    }

    public function __toString() : string
    {
        return implode('\\', $this->parts);
    }

    /**
     * @param array $parts
     */
    private function ensureNamespaceIsValid(array $parts)
    {
        if (Util::array_once($parts, function ($value, $index) {
            return !is_string($value);
        })
        ) {
            throw new \InvalidArgumentException('Invalid namespace');
        }
    }
}
