<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencyFactory
 */
class DependencyFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var DependencyFactory */
    private $clazzFactory;

    public function setUp()
    {
        $this->clazzFactory = new DependencyFactory();
    }

    public function testInvalidClassReturnsNullDependency()
    {
        $this->assertInstanceOf(
            NullDependency::class,
            $this->clazzFactory->createClazzFromStringArray(['/'])
        );
    }

    public function testCreatesClazzWithEmptyNamespace()
    {
        $this->assertEquals(new Clazz('Test', new Namespaze([])), $this->clazzFactory->createClazzFromStringArray(['Test']));
    }

    public function testCreateClazzWithNamespace()
    {
        $this->assertEquals(
            new Clazz('Test', new Namespaze(['Mihaeu', 'PhpDependencies'])),
            $this->clazzFactory->createClazzFromStringArray(['Mihaeu', 'PhpDependencies', 'Test'])
        );
    }

    public function testCreateInterfaze()
    {
        $this->assertEquals(new AbstractClazz('Test', new Namespaze([])), $this->clazzFactory->createAbstractClazzFromStringArray(['Test']));
    }

    public function testCreateAbstractClazz()
    {
        $this->assertEquals(new Interfaze('Test', new Namespaze([])), $this->clazzFactory->createInterfazeFromStringArray(['Test']));
    }

    public function testCreateTrait()
    {
        $this->assertEquals(new Trait_('Test', new Namespaze([])), $this->clazzFactory->createTraitFromStringArray(['Test']));
    }
}
