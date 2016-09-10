<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;

class DependencyHelper
{
    /**
     * Converts dependencies written in string format into a proper
     * DependencyPairCollection.
     *
     * @param string $input format:
     *
     *      DepA --> DepB, DepC
     *      DepC --> DepD, DepE
     *
     * @return DependencyPairCollection
     *
     * @throws \InvalidArgumentException
     */
    public static function convert(string $input) : DependencyPairCollection
    {
        $lines = preg_split('/\v+/', $input, -1, PREG_SPLIT_NO_EMPTY);
        return array_reduce($lines, function (DependencyPairCollection $collection, string $line) {
            return preg_match('/^ +$/', $line) ? $collection : $collection->add(self::dependencyPair($line));
        }, new DependencyPairCollection());
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

    /**
     * @param string $input format: NamespaceA\\a
     *
     * @return Namespaze
     */
    public static function namespaze(string $input) : Namespaze
    {
        return new Namespaze(explode('\\', $input));
    }

    /**
     * @param string $input format: NamespaceA\\ClassA --> NamespaceB\\ClassB, NamespaceC\\ClassC
     *
     * @return DependencyPair
     */
    public static function dependencyPair(string $input) : DependencyPair
    {
        $tokens = explode('-->', str_replace(' ', '', $input));
        return new DependencyPair(self::dependency($tokens[0]), self::dependencySet($tokens[1]));
    }

    /**
     * @param string $input format: NamespaceA\\ClassA, NamespaceB\\ClassB, NamespaceC\\ClassC
     *
     * @return DependencySet
     */
    public static function dependencySet(string $input) : DependencySet
    {
        if ($input === '_') {
            return new DependencySet();
        }
        $set = new DependencySet();
        foreach (explode(',', $input) as $token) {
            $set = $set->add(self::dependency($token));
        }
        return $set;
    }

    private static function dependency(string $input) : Dependency
    {
        $input = str_replace(' ', '', $input);
        if (strpos($input, '_') === 0) {
            return self::namespaze(substr($input, 1));
        }
        return self::clazz($input);
    }
}
