<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\PhpFileCollection
 * @covers Mihaeu\PhpDependencies\FunctionalEach
 *
 * @uses Mihaeu\PhpDependencies\PhpFile
 */
class PhpFileCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testEquals()
    {
        $file = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $collection1 = new PhpFileCollection();
        $collection1->add($file);
        $collection2 = new PhpFileCollection();
        $collection2->add($file);
        $this->assertTrue($collection1->equals($collection2));
    }

    public function testNotEquals()
    {
        $collection1 = new PhpFileCollection();
        $collection1->add(new PhpFile(new \SplFileInfo(sys_get_temp_dir())));
        $collection2 = new PhpFileCollection();
        $collection2->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $this->assertFalse($collection1->equals($collection2));
    }

    public function testGet()
    {
        $file = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $collection1 = new PhpFileCollection();
        $collection1->add($file);
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
        $collection1 = new PhpFileCollection();
        $collection1->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $collection1->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $collection1->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $this->assertCount(3, $collection1);
    }

    public function testIterable()
    {
        $collection1 = new PhpFileCollection();
        $file1 = new PhpFile(new \SplFileInfo(__DIR__));
        $collection1->add($file1);
        $file2 = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $collection1->add($file2);
        $array = iterator_to_array($collection1);
        $this->assertEquals($array[0], $file1);
        $this->assertEquals($array[1], $file2);
    }

    public function testEach()
    {
        $collection1 = new PhpFileCollection();
        $collection1->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $collection1->each(function (PhpFile $file) {
            $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $file);
        });
    }

    public function testMapToArray()
    {
        $collection1 = new PhpFileCollection();
        $collection1->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $collection1->add(new PhpFile(new \SplFileInfo(__DIR__)));
        $result = $collection1->mapToArray(function (PhpFile $file) {
            return $file;
        });
        $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $result[0]);
        $this->assertEquals(new PhpFile(new \SplFileInfo(__DIR__)), $result[1]);
    }
}
