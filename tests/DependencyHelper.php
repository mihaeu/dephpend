<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyHelper
{
    /**
     * Converts dependencies written in string format into a proper
     * DependencyPairCollection.
     *
     * @param string $input format:
     *
     *      DepA --> DepB
     *      DepC --> DepD
     *
     * @return DependencyPairCollection
     *
     * @throws \InvalidArgumentException
     */
    public static function convert(string $input) : DependencyPairCollection
    {
        $tokens = array_values(array_filter(preg_split('/[\s]/', $input)));
        if (count($tokens) % 3 !== 0) {
            throw new \InvalidArgumentException(
                'Number of arguments not correct, '
                .'write pairs of X\\ClassX --> Y\\ClassY separated by new lines.'
            );
        }

        $dependencies = new DependencyPairCollection();
        for ($i = 0, $len = count($tokens); $i < $len; $i += 3) {
            $dependencies = $dependencies->add(new DependencyPair(
                self::clazz($tokens[$i]),
                self::clazz($tokens[$i + 2])
            ));
        }
        return $dependencies;
    }

    /**
     * @param string $input format: NamespaceA\\ClassA
     *
     * @return Clazz
     */
    public static function clazz(string $input) : Clazz
    {
        return (new DependencyFactory())->createClazzFromStringArray((explode('\\', $input)));
    }
}
