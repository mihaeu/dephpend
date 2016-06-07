<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\PhpFileCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 *
 * @uses Mihaeu\PhpDependencies\PhpFile
 */
class PhpFileCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testEquals()
    {
        $file = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $collection1 = (new PhpFileCollection())
            ->add($file);
        $collection2 = (new PhpFileCollection())
            ->add($file);
        $this->assertTrue($collection1->equals($collection2));
    }

    public function testNotEquals()
    {
        $collection1 = (new PhpFileCollection())
            ->add(new PhpFile(new \SplFileInfo(sys_get_temp_dir())));
        $collection2 = (new PhpFileCollection())
            ->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $this->assertFalse($collection1->equals($collection2));
    }

    public function testGet()
    {
        $file = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $collection1 = (new PhpFileCollection())->add($file);
        $this->assertSame($file, $collection1->get(0));
    }

    public function testCannotGet()
    {
        $collection1 = new PhpFileCollection();
        $this->expectException(IndexOutOfBoundsException::class);
        $collection1->get(0);
    }

    public function testCount0WhenEmpty()
    {
        $collection1 = new PhpFileCollection();
        $this->assertCount(0, $collection1);
    }

    public function testCount()
    {
        $collection1 = (new PhpFileCollection())
            ->add(new PhpFile(new \SplFileInfo(__DIR__)))
            ->add(new PhpFile(new \SplFileInfo(__DIR__)))
            ->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $this->assertCount(3, $collection1);
    }

    public function testEach()
    {
        $collection1 = (new PhpFileCollection())->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $collection1->each(function (PhpFile $file) {
            $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $file);
        });
    }

    public function testMapToArray()
    {
        $collection1 = (new PhpFileCollection())
            ->add(new PhpFile(new \SplFileInfo(__DIR__)))
            ->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $result = $collection1->mapToArray(function (PhpFile $file) {
            return $file;
        });
        $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $result[0]);
        $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $result[1]);
    }
}
