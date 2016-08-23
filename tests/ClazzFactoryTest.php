<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ClazzFactory
 */
class ClazzFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClazzFactory */
    private $clazzFactory;

    public function setUp()
    {
        $this->clazzFactory = new ClazzFactory();
    }

    public function testCreatesClazzWithEmptyNamespace()
    {
        $this->assertEquals(new Clazz('Test', new ClazzNamespace([])), $this->clazzFactory->createFromStringArray(['Test']));
    }

    public function testCreateClazzWithNamespace()
    {
        $this->assertEquals(
            new Clazz('Test', new ClazzNamespace(['Mihaeu', 'PhpDependencies'])),
            $this->clazzFactory->createFromStringArray(['Mihaeu', 'PhpDependencies', 'Test'])
        );
    }
}
