<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Clazz
 */
class ClazzTest extends \PHPUnit_Framework_TestCase
{
    public function testHasValue()
    {
        $this->assertEquals('Name', new Clazz('Name'));
        $this->assertEquals('Name', (new Clazz('Name'))->toString());
    }

    public function testEquals()
    {
        $this->assertTrue((new Clazz('A'))->equals(new Clazz('A')));
    }

    public function testDetectsIfClassHasNamespace()
    {
        $this->assertTrue((new Clazz('Class', new ClazzNamespace(['A'])))->hasNamespace());
    }

    public function testDetectsIfClassHasNoNamespace()
    {
        $this->assertFalse((new Clazz('Class'))->hasNamespace());
    }
}
