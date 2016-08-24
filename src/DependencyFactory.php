<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyFactory
{
    public function createClazzFromStringArray(array $parts) : Clazz
    {
        return new Clazz(
            array_slice($parts, -1)[0],
            new ClazzNamespace(array_slice($parts, 0, -1))
        );
    }
}
