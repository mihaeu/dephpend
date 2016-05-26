<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

/**
 * @covers mihaeu\phpDependencies\PhpFileCollection
 *
 * @uses mihaeu\phpDependencies\PhpFile
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
}
