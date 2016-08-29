<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyHelper
{
    /**
     * Converts dependencies written in string format into a proper
     * DependencyPairCollection.
     *
     * @param string $input Written in the following format:
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

        $factory = new DependencyFactory();
        $dependencies = new DependencyPairCollection();
        for ($i = 0, $len = count($tokens); $i < $len; $i += 3) {
            $dependencies = $dependencies->add(new DependencyPair(
                $factory->createClazzFromStringArray((explode('\\', $tokens[$i]))),
                $factory->createClazzFromStringArray((explode('\\', $tokens[$i + 2])))
            ));
        }
        return $dependencies;
    }
}
