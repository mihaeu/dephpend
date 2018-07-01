<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

/**
 * @covers Mihaeu\PhpDependencies\Util\DependencyContainer
 */
class DependencyContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return \Generator
     * @throws \ReflectionException
     */
    public function provideMethods(): array
    {
        $reflectionClass = new \ReflectionClass(DependencyContainer::class);
        $methods = [];
        foreach ($reflectionClass->getMethods() as $method) {
            if (!$method->hasReturnType()) {
                continue;
            }
            $methods[] = [$method->getName(), (string) $method->getReturnType()];
        }
        return $methods;
    }

    /**
     * @dataProvider provideMethods
     * @param string $methodName
     * @param string $expectedReturnType
     */
    public function testCanInstantiateAllDependencies(string $methodName, string $expectedReturnType)
    {
        assertInstanceOf($expectedReturnType, (new DependencyContainer([]))->{$methodName}());
    }
}
