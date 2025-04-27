<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;

/**
 * @covers Mihaeu\PhpDependencies\Util\DependencyContainer
 */
class DependencyContainerTest extends TestCase
{
    /**
     * @return list<array{non-empty-string, non-empty-string}>
     * @throws ReflectionException
     */
    public function provideMethods(): array
    {
        $reflectionClass = new ReflectionClass(DependencyContainer::class);
        $methods = [];
        foreach ($reflectionClass->getMethods() as $method) {
            if (! $method->hasReturnType()) {
                continue;
            }
            $methods[] = [$method->getName(), (string) $method->getReturnType()];
        }
        return $methods;
    }

    /**
     * @dataProvider provideMethods
     */
    public function testCanInstantiateAllDependencies(string $methodName, string $expectedReturnType): void
    {
        $this->assertInstanceOf($expectedReturnType, (new DependencyContainer([]))->{$methodName}());
    }
}
