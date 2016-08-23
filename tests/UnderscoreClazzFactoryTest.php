<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\UnderscoreClazzFactory
 */
class UnderscoreClazzFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClazzFactory */
    private $clazzFactory;

    public function setUp()
    {
        $this->clazzFactory = new UnderscoreClazzFactory();
    }

    public function testNoUnderscoresNoNamespaces()
    {
        $this->assertEquals(new Clazz('Test'), $this->clazzFactory->createFromStringArray(['Test']));
    }

    public function testPhp5NamespacesStillDetected()
    {
        $this->assertEquals(
            new Clazz('Test', new ClazzNamespace(['A'])),
            $this->clazzFactory->createFromStringArray(['A', 'Test'])
        );
    }

    public function testDetectsNamespacesFromClassWithOnlyUnderscores()
    {
        $this->assertEquals(
            new Clazz('Test', new ClazzNamespace(['A', 'b', 'c'])),
            $this->clazzFactory->createFromStringArray(['A_b_c_Test'])
        );
    }

    public function testMixedNamespacesDetected()
    {
        $this->assertEquals(
            new Clazz('Test', new ClazzNamespace(['A', 'B'])),
            $this->clazzFactory->createFromStringArray(['A', 'B_Test'])
        );
    }
}
