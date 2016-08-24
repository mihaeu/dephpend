<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyFactory
 */
class DependencyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyFactory */
    private $clazzFactory;

    public function setUp()
    {
        $this->clazzFactory = new DependencyFactory();
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
}
