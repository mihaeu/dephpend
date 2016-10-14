<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser
 */
class XDebugFunctionTraceAnalyserTest extends \PHPUnit_Framework_TestCase
{
    /** @var XDebugFunctionTraceAnalyser */
    private $xDebugFunctionTraceAnalyser;

    public function setUp()
    {
        $this->xDebugFunctionTraceAnalyser = new XDebugFunctionTraceAnalyser();
    }

    public function testAnalyse()
    {
        //        var_dump($this->xDebugFunctionTraceAnalyser->analyse(new \SplFileInfo('/tmp/trace.2955610183.xt'))->toString());
        $this->assertTrue(true);
    }
    
//    public function testThrowsExceptionIfFileNotReadable()
//    {
//        $this->expectException(\InvalidArgumentException::class);
//        $this->xDebugFunctionTraceAnalyser->analyse(new \SplFileInfo('/tmp'));
//    }
}
