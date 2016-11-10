<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Util\DI;
use PhpParser\Error;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Application
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /** @var Application */
    private $application;

    /** @var DI */
    private $dI;

    const XDEBUG_WARNING = '<fg=black;bg=yellow>You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug</>';

    public function setUp()
    {
        $this->dI = $this->createMock(DI::class);
        $_SERVER['argv'] = ['', 'text', sys_get_temp_dir()];
        $this->application = new Application('', '', $this->dI);
    }

    public function testWarningIfXDebugEnabled()
    {
        $input = $this->createMock(Input::class);
        $input->method('hasParameterOption')->willReturn(false);
        $output = $this->createMock(Output::class);

        // not sure how to mock this, so we test only one case
        if (!extension_loaded('xdebug')) {
            $output->expects($this->never())->method('writeln')->with(self::XDEBUG_WARNING);
        } else {
            $output->expects($this->once())->method('writeln');
        }
        $this->application->doRun($input, $output);
    }

    public function testPrintsErrorMessageIfParserThrowsException()
    {
        $input = $this->createMock(Input::class);
        $input->method('hasParameterOption')->willReturn(false);
        $output = $this->createMock(Output::class);
        $input->method('getFirstArgument')->willThrowException(new Error('Test'));

        $expectedMessage = '<error>Sorry, we could not analyse your dependencies, '
            . 'because the sources contain syntax errors:' . PHP_EOL . PHP_EOL
            . 'Test on unknown line<error>';

        if (!extension_loaded('xdebug')) {
            $output->expects($this->once())->method('writeln')->with($expectedMessage);
        } else {
            $output->expects($this->exactly(2))->method('writeln')->withConsecutive(
                [self::XDEBUG_WARNING],
                [$expectedMessage]
            );
        }
        $this->application->doRun($input, $output);
    }

    public function testAddsDynamicDepencies()
    {
        $dI = $this->createMock(DI::class);
        $dynamicAnalyser = $this->createMock(XDebugFunctionTraceAnalyser::class);
        $dI->method('xDebugFunctionTraceAnalyser')->willReturn($dynamicAnalyser);
        $_SERVER['argv'] = ['', 'text', sys_get_temp_dir(), '--dynamic='.sys_get_temp_dir()];

        $input = $this->createMock(Input::class);
        $input->method('hasParameterOption')->willReturn(false);
        $output = $this->createMock(Output::class);
        $dynamicAnalyser->expects($this->once())->method('analyse');
        (new Application('', '', $dI))->doRun($input, $output);
    }

    public function testDoesNotAnalyseAnythingWhenNotProvidingCommands()
    {
        $_SERVER['argv'] = [''];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->createMock(DI::class)))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesDsmInput()
    {
        $_SERVER['argv'] = ['', 'dsm', sys_get_temp_dir(), '--format=html'];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->createMock(DI::class)))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesUmlInput()
    {
        $_SERVER['argv'] = ['', 'uml', sys_get_temp_dir(), '--output=test.png'];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->createMock(DI::class)))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesMetricInput()
    {
        $_SERVER['argv'] = ['', 'metrics', sys_get_temp_dir()];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->createMock(DI::class)))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }
}
