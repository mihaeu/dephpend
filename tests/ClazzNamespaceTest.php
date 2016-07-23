<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ClazzNamespace
 */
class ClazzNamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testAcceptsEmptyNamespace()
    {
        $this->assertEquals('', new ClazzNamespace([]));
    }

    public function testAcceptsValidNamespaceParts()
    {
        $this->assertEquals('a\b\c', new ClazzNamespace(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ClazzNamespace([1]);
    }
}
