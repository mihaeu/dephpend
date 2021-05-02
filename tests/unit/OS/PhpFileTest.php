<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Exceptions\FileDoesNotExistException;
use Mihaeu\PhpDependencies\Exceptions\FileIsNotReadableException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @covers Mihaeu\PhpDependencies\OS\PhpFile
 * @covers Mihaeu\PhpDependencies\Exceptions\FileIsNotReadableException
 * @covers Mihaeu\PhpDependencies\Exceptions\FileDoesNotExistException
 */
class PhpFileTest extends TestCase
{
    public function testEquals(): void
    {
        $file1 = new PhpFile(new SplFileInfo(sys_get_temp_dir()));
        $file2 = new PhpFile(new SplFileInfo(sys_get_temp_dir()));
        assertTrue($file1->equals($file2));
    }

    public function testNotEquals(): void
    {
        $file1 = new PhpFile(new SplFileInfo(sys_get_temp_dir()));
        $file2 = new PhpFile(new SplFileInfo(__DIR__));
        assertFalse($file1->equals($file2));
    }

    public function testReturnsCode(): void
    {
        $code = '<?php echo "Hello World";';
        $mockDir = vfsStream::setup('root', null, [
            'someFile.php' => $code,
        ]);
        $file = new PhpFile(new SplFileInfo($mockDir->url().'/someFile.php'));
        assertEquals($code, $file->code());
    }

    public function testToString(): void
    {
        Assert::assertStringContainsString('PhpFileTest.php', (new PhpFile(new SplFileInfo(__FILE__)))->__toString());
    }

    public function testThrowsExceptionsIfFileDoesNotExist(): void
    {
        $this->expectException(FileDoesNotExistException::class);
        new PhpFile(new SplFileInfo('akdsjajdlsad'));
    }

    public function testThrowsExceptionIfFileIsNotReadable(): void
    {
        $tmpFiles = $this->createMock(SplFileInfo::class);
        $tmpFiles->expects($this->once())->method('isFile')->willReturn(true);
        $tmpFiles->expects($this->once())->method('isReadable')->willReturn(false);

        $this->expectException(FileIsNotReadableException::class);
        new PhpFile($tmpFiles);
    }
}
