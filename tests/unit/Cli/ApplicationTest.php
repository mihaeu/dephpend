<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\OS\DotWrapper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Application
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $application;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    const XDEBUG_WARNING = '<fg=black;bg=yellow>You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug</>';

    public function setUp()
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $_SERVER['argv'] = ['', 'text', sys_get_temp_dir()];
        $this->application = new Application('', '', $this->dispatcher);
    }

    public function testWarningIfXDebugEnabled()
    {
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);

        // not sure how to mock this, so we test only one case
        if (!extension_loaded('xdebug')) {
            $output->expects($this->never())->method('writeln')->with(self::XDEBUG_WARNING);
        } else {
            $output->expects($this->exactly(2))->method('writeln');
        }
        $this->application->doRun($input, $output);
    }

    public function testPrintsErrorMessageIfParserThrowsException()
    {
        $input = $this->createMock(Input::class);
        $input->method('hasParameterOption')->willThrowException(new \PhpParser\Error('Test'));
        $output = $this->createMock(Output::class);

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

    public function testValidatesDsmInput()
    {
        $_SERVER['argv'] = ['', 'dsm', sys_get_temp_dir(), '--format=html'];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesUmlInput()
    {
        $_SERVER['argv'] = ['', 'uml', sys_get_temp_dir(), '--output=test.png'];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesMetricInput()
    {
        $_SERVER['argv'] = ['', 'metrics', sys_get_temp_dir()];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesDotInput()
    {
        $_SERVER['argv'] = ['', 'dot', sys_get_temp_dir()];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        $this->assertEquals(0, $returnCode);
    }

    public function testCommandWithHelpOptionProvidesHelpForDotCommand()
    {
        $input = new ArgvInput(['', 'dot', '--help']);
        $output = new BufferedOutput();
        $application = new Application('', '', $this->dispatcher);
        $application->add(new DotCommand($this->createMock(DotWrapper::class)));
        $application->doRun($input, $output);
        $this->assertContains('dot [options]', $output->fetch());
    }

    public function testNoCommandWithVersionOptionWritesVersion()
    {
        $input = new ArgvInput(['', '--version']);
        $output = new BufferedOutput();
        (new Application('Test', '4.2.0', $this->dispatcher))->doRun($input, $output);
        $this->assertRegExp('/\d+\.\d+\.\d+/', $output->fetch());
    }

    public function testNoCommandWithHelpOptionWritesHelp()
    {
        $input = new ArgvInput(['', '--help']);
        $output = new BufferedOutput();
        $application = new Application('', '', $this->dispatcher);
        $application->doRun($input, $output);
        $this->assertContains('list [options]', $output->fetch());
    }

    public function testHelpOptionWithAnsiOptionPrintsHelp()
    {
        $input = new ArgvInput(['', '--help', '--ansi']);
        $output = new BufferedOutput();

        (new Application('', '', $this->dispatcher))->doRun($input, $output);
        $this->assertContains('list [options]', $output->fetch());
    }
}
