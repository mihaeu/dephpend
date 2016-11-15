<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\DotNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\DotFormatter;
use Mihaeu\PhpDependencies\OS\DotWrapper;
use Mihaeu\PhpDependencies\OS\ShellWrapper;
use org\bovigo\vfs\vfsStream;

/**
 * @covers Mihaeu\PhpDependencies\OS\DotWrapper
 */
class DotWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var ShellWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $shellWrapper;

    /** @var DotFormatter|\PHPUnit_Framework_MockObject_MockObject */
    private $dotFormatter;

    /** @var DotWrapper */
    private $dotWrapper;

    public function setUp()
    {
        $this->shellWrapper = $this->createMock(ShellWrapper::class);
        $this->dotFormatter = $this->createMock(DotFormatter::class);
        $this->dotWrapper = new DotWrapper($this->dotFormatter, $this->shellWrapper);
    }

    public function testThrowsExceptionIfDotIsNotInstalled()
    {
        $this->shellWrapper->method('run')->willReturn(1);

        $this->expectException(DotNotInstalledException::class);
        $this->dotWrapper->generate(new DependencyMap(), new \SplFileInfo(__FILE__));
    }
    
    public function testRunsDot()
    {
        $root = vfsStream::setup()->url();

        $this->shellWrapper
            ->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(
                ['dot -V'],
                ['dot -O -Tpng '.$root.'/test']
            )
        ;
        $this->dotWrapper->generate(new DependencyMap(), new \SplFileInfo($root.'/test.png'), true);
    }

    public function testKeepsDotFiles()
    {
        $root = vfsStream::setup()->url();
        $testFile = new \SplFileInfo($root.'/test');
        $this->assertFileNotExists($testFile->getPathname());
        $this->dotWrapper->generate(new DependencyMap(), new \SplFileInfo($testFile->getPathname()), true);
        $this->assertFileExists($testFile->getPathname());
    }


    public function testRemovesDotFiles()
    {
        $root = vfsStream::setup()->url();
        $testFile = new \SplFileInfo($root.'/test');
        $this->dotWrapper->generate(new DependencyMap(), new \SplFileInfo($root.'/test.png'), false);
        $this->assertFileNotExists($testFile->getPathname());
    }
}
