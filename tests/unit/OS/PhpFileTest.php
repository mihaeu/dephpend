<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Exceptions\FileDoesNotExistException;
use Mihaeu\PhpDependencies\Exceptions\FileIsNotReadableException;
use org\bovigo\vfs\vfsStream;

/**
 * @covers Mihaeu\PhpDependencies\OS\PhpFile
 * @covers Mihaeu\PhpDependencies\Exceptions\FileIsNotReadableException
 * @covers Mihaeu\PhpDependencies\Exceptions\FileDoesNotExistException
 */
class PhpFileTest extends \PHPUnit\Framework\TestCase
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

    public function testReturnsCode()
    {
        $code = '<?php echo "Hello World";';
        $mockDir = vfsStream::setup('root', null, [
            'someFile.php' => $code,
        ]);
        $file = new PhpFile(new \SplFileInfo($mockDir->url().'/someFile.php'));
        $this->assertEquals($code, $file->code());
    }

    public function testToString()
    {
        $this->assertContains('PhpFileTest.php', (new PhpFile(new \SplFileInfo(__FILE__)))->__toString());
    }

    public function testThrowsExceptionsIfFileDoesNotExist()
    {
        $this->expectException(FileDoesNotExistException::class);
        new PhpFile(new \SplFileInfo('akdsjajdlsad'));
    }

    public function testThrowsExceptionIfFileIsNotReadable()
    {
        touch(sys_get_temp_dir().'/unreadable');
        chmod(sys_get_temp_dir().'/unreadable', 0000);

        $this->expectException(FileIsNotReadableException::class);
        new PhpFile(new \SplFileInfo(sys_get_temp_dir() . '/unreadable'));
    }
}
