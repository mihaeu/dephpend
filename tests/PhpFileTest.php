<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

/**
 * @covers mihaeu\phpDependencies\PhpFile
 */
class PhpFileTest extends \PHPUnit_Framework_TestCase
{
    public function testEquals()
    {
        $file1 = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $file2 = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $this->assertTrue($file1->equals($file2));
    }

    public function testNotEquals()
    {
        $file1 = new PhpFile(new \SplFileInfo(sys_get_temp_dir()));
        $file2 = new PhpFile(new \SplFileInfo(__DIR__));
        $this->assertFalse($file1->equals($file2));
    }
}
