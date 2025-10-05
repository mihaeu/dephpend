<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Dependencies\DependencyFactory::class)]
class DependencyFactoryTest extends TestCase
{
    /** @var DependencyFactory */
    private $clazzFactory;

    protected function setUp(): void
    {
        $this->clazzFactory = new DependencyFactory();
    }

    public function testInvalidClassReturnsNullDependency(): void
    {
        $this->assertInstanceOf(
            NullDependency::class,
            $this->clazzFactory->createClazzFromStringArray(['/'])
        );
    }

    public function testCreatesClazzWithEmptyNamespace(): void
    {
        $this->assertEquals(new Clazz('Test', new Namespaze([])), $this->clazzFactory->createClazzFromStringArray(['Test']));
    }

    public function testCreateClazzWithNamespace(): void
    {
        $this->assertEquals(
            new Clazz('Test', new Namespaze(['Mihaeu', 'PhpDependencies'])),
            $this->clazzFactory->createClazzFromStringArray(['Mihaeu', 'PhpDependencies', 'Test'])
        );
    }

    public function testCreateInterfaze(): void
    {
        $this->assertEquals(new AbstractClazz('Test', new Namespaze([])), $this->clazzFactory->createAbstractClazzFromStringArray(['Test']));
    }

    public function testCreateAbstractClazz(): void
    {
        $this->assertEquals(new Interfaze('Test', new Namespaze([])), $this->clazzFactory->createInterfazeFromStringArray(['Test']));
    }

    public function testCreateTrait(): void
    {
        $this->assertEquals(new Trait_('Test', new Namespaze([])), $this->clazzFactory->createTraitFromStringArray(['Test']));
    }
}
