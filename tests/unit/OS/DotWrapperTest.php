<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\DotNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\DotFormatter;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileInfo;

/**
 * @covers Mihaeu\PhpDependencies\OS\DotWrapper
 * @covers \Mihaeu\PhpDependencies\Exceptions\DotNotInstalledException
 */
class DotWrapperTest extends TestCase
{
    /** @var ShellWrapper&MockObject */
    private $shellWrapper;

    /** @var DotFormatter&MockObject */
    private $dotFormatter;

    /** @var DotWrapper */
    private $dotWrapper;

    protected function setUp(): void
    {
        $this->shellWrapper = $this->createMock(ShellWrapper::class);
        $this->dotFormatter = $this->createMock(DotFormatter::class);
        $this->dotWrapper = new DotWrapper($this->dotFormatter, $this->shellWrapper);
    }

    public function testThrowsExceptionIfDotIsNotInstalled(): void
    {
        $this->shellWrapper->method('run')->willReturn(1);

        $this->expectException(DotNotInstalledException::class);
        $this->dotWrapper->generate(new DependencyMap(), new SplFileInfo(__FILE__));
    }

    public function testRunsDot(): void
    {
        $root = vfsStream::setup()->url();

        $this->shellWrapper
            ->expects($this->exactly(2))
            ->method('run')
            ->willReturnCallback(function ($command) use ($root) {
                static $callCount = 0;
                $callCount++;
                
                if ($callCount === 1) {
                    $this->assertEquals('dot -V', $command);
                } else if ($callCount === 2) {
                    $this->assertEquals('dot -O -Tpng '.$root.'/test', $command);
                }
                
                return 0;
            })
        ;
        $this->dotWrapper->generate(new DependencyMap(), new SplFileInfo($root.'/test.png'), true);
    }

    public function testKeepsDotFiles(): void
    {
        $root = vfsStream::setup()->url();
        $testFile = new SplFileInfo($root.'/test');
        $this->assertFalse(file_exists($testFile->getPathname()));
        $this->dotWrapper->generate(new DependencyMap(), new SplFileInfo($testFile->getPathname()), true);
        $this->assertTrue(file_exists($testFile->getPathname()));
    }


    public function testRemovesDotFiles(): void
    {
        $root = vfsStream::setup()->url();
        $testFile = new SplFileInfo($root.'/test');
        $this->dotWrapper->generate(new DependencyMap(), new SplFileInfo($root.'/test.png'), false);
        $this->assertFalse(file_exists($testFile->getPathname()));
    }
}
