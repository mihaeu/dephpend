<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\DependencyHelper;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser
 */
class XDebugFunctionTraceAnalyserTest extends \PHPUnit_Framework_TestCase
{
    /** @var XDebugFunctionTraceAnalyser */
    private $xDebugFunctionTraceAnalyser;

    /** @var SplFileInfo */
    private $tempFile;

    public function setUp()
    {
        $this->xDebugFunctionTraceAnalyser = new XDebugFunctionTraceAnalyser();
        $this->tempFile = new \SplFileInfo(sys_get_temp_dir().'/'.'dephpend-trace.sample');
    }

    public function tearDown()
    {
        unlink($this->tempFile->getPathname());
    }

    public function testAnalyse()
    {
        $this->writeContent([
            [0, 1, 2, 3, 4, 'B->c', 6, 7, 8, 9, 10, 'class A'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'class A'],
        ]);
        $this->assertEquals(
            DependencyHelper::map('
                B --> A
                D --> A
            '), $this->xDebugFunctionTraceAnalyser->analyse($this->tempFile)
        );
    }

    public function testAnalyseIgnoresScalarValues()
    {
        $this->writeContent([
            [0, 1, 2, 3, 4, 'B->c', 6, 7, 8, 9, 10, '???', 'class A'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'string(10)'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'long'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'true'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'false'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'null'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'int'],
            [0, 1, 2, 3, 4, 'D->c', 6, 7, 8, 9, 10, 'resource'],
        ]);
        $this->assertEquals(
            DependencyHelper::map('
                B --> A
            '), $this->xDebugFunctionTraceAnalyser->analyse($this->tempFile)
        );
    }

    public function testThrowsExceptionIfFileCannotBeOpened()
    {
        touch($this->tempFile->getPathname());
        chmod($this->tempFile->getPathname(), 0000);
        $this->expectException(\InvalidArgumentException::class);
        $this->xDebugFunctionTraceAnalyser->analyse($this->tempFile);
    }

    private function createContent(array $data) : string
    {
        return array_reduce($data, function (string $carry, array $lineParts) {
            return $carry.implode("\t", $lineParts).PHP_EOL;
        }, '');
    }

    private function writeContent(array $data)
    {
        file_put_contents($this->tempFile->getPathname(), $this->createContent($data));
    }
}
