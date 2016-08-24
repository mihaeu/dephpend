<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class UnderscoreDependencyFactory extends DependencyFactory
{
    /**
     * {@inheritdoc}
     */
    public function createClazzFromStringArray(array $parts) : Clazz
    {
        if (count($parts) === 1) {
            return parent::createClazzFromStringArray(explode('_', $parts[0]));
        }

        $classParts = explode('_', $parts[count($parts) - 1]);
        $partsWithoutClass = array_slice($parts, 0, -1);
        $newParts = array_merge($partsWithoutClass, $classParts);

        return parent::createClazzFromStringArray($newParts);
    }
}
