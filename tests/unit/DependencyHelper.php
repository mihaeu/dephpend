<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;

class DependencyHelper
{
    /**
     * Converts dependencies written in string format into a proper
     * DependencyMap.
     *
     * @param string $input format:
     *
     *      DepA --> DepB, DepC
     *      DepC --> DepD, DepE
     *
     * @return DependencyMap
     *
     * @throws InvalidArgumentException
     */
    public static function map(string $input): DependencyMap
    {
        $lines = preg_split('/\v+/', $input, -1, PREG_SPLIT_NO_EMPTY);
        $array_reduce = array_reduce(
            $lines,
            function (DependencyMap $map, string $line) {
                if (empty(trim($line))) {
                    return $map;
                }
                $dependencyPair = self::dependencyPair($line);
                return $map->addSet($dependencyPair[0], $dependencyPair[1]);
            },
            new DependencyMap()
        );
        return $array_reduce;
    }

    /**
     * @param string $input format: NamespaceA\\ClassA
     *
     * @return Clazz
     */
    public static function clazz(string $input): Dependency
    {
        return (new DependencyFactory())->createClazzFromStringArray(explode('\\', $input));
    }

    /**
     * @param string $input format: NamespaceA\\a
     *
     * @return Namespaze
     */
    public static function namespaze(string $input): Namespaze
    {
        return new Namespaze(explode('\\', $input));
    }

    /**
     * @param string $input format: NamespaceA\\ClassA --> NamespaceB\\ClassB, NamespaceC\\ClassC
     *
     * @return array
     */
    public static function dependencyPair(string $input): array
    {
        $tokens = explode('-->', str_replace(' ', '', $input));
        return [self::dependency($tokens[0]), self::dependencySet($tokens[1])];
    }

    /**
     * @param string $input format: NamespaceA\\ClassA, NamespaceB\\ClassB, NamespaceC\\ClassC
     *
     * @return DependencySet
     */
    public static function dependencySet(string $input): DependencySet
    {
        $set = new DependencySet();
        if ($input === '_') {
            return $set;
        }

        foreach (explode(',', $input) as $token) {
            $set = $set->add(self::dependency($token));
        }
        return $set;
    }

    private static function dependency(string $input): Dependency
    {
        $input = str_replace(' ', '', $input);
        if (strpos($input, '_') === 0) {
            return self::namespaze(substr($input, 1));
        }
        return self::clazz($input);
    }
}
