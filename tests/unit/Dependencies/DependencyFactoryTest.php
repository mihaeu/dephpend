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
        assertInstanceOf(
            NullDependency::class,
            $this->clazzFactory->createClazzFromStringArray(['/'])
        );
    }

    public function testCreatesClazzWithEmptyNamespace()
    {
        assertEquals(new Clazz('Test', new Namespaze([])), $this->clazzFactory->createClazzFromStringArray(['Test']));
    }

    public function testCreateClazzWithNamespace()
    {
        assertEquals(
            new Clazz('Test', new Namespaze(['Mihaeu', 'PhpDependencies'])),
            $this->clazzFactory->createClazzFromStringArray(['Mihaeu', 'PhpDependencies', 'Test'])
        );
    }

    public function testCreateInterfaze()
    {
        assertEquals(new AbstractClazz('Test', new Namespaze([])), $this->clazzFactory->createAbstractClazzFromStringArray(['Test']));
    }

    public function testCreateAbstractClazz()
    {
        assertEquals(new Interfaze('Test', new Namespaze([])), $this->clazzFactory->createInterfazeFromStringArray(['Test']));
    }

    public function testCreateTrait()
    {
        assertEquals(new Trait_('Test', new Namespaze([])), $this->clazzFactory->createTraitFromStringArray(['Test']));
    }
}
