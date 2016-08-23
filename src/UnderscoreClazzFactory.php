<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class UnderscoreClazzFactory extends ClazzFactory
{
    /**
     * {@inheritdoc}
     */
    public function createFromStringArray(array $parts) : Clazz
    {
        if (count($parts) === 1) {
            return parent::createFromStringArray(explode('_', $parts[0]));
        }

        $classParts = explode('_', $parts[count($parts) - 1]);
        $partsWithoutClass = array_slice($parts, 0, -1);
        $newParts = array_merge($partsWithoutClass, $classParts);

        return parent::createFromStringArray($newParts);
    }
}
