<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversClass(\Mihaeu\PhpDependencies\Util\DependencyContainer::class)]
class DependencyContainerTest extends TestCase
{
    /**
     * @return list<array{non-empty-string, non-empty-string}>
     * @throws ReflectionException
     */
    public static function provideMethods(): array
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

    #[DataProvider('provideMethods')]
    public function testCanInstantiateAllDependencies(string $methodName, string $expectedReturnType): void
    {
        $this->assertInstanceOf($expectedReturnType, (new DependencyContainer([]))->{$methodName}());
    }
}
