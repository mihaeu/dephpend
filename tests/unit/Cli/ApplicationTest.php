<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Exceptions\ParserException;
use Mihaeu\PhpDependencies\OS\DotWrapper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Application
 * @covers \Mihaeu\PhpDependencies\Exceptions\ParserException
 */
class ApplicationTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    private const XDEBUG_WARNING = '<fg=black;bg=yellow>You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug</>';

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $_SERVER['argv'] = ['', 'text', sys_get_temp_dir()];
        $this->application = new Application('', '', $this->dispatcher);
    }

    public function testWarningIfXDebugEnabled(): void
    {
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);

        // not sure how to mock this, so we test only one case
        if (!extension_loaded('xdebug')) {
            $output->expects(never())->method('writeln')->with(self::XDEBUG_WARNING);
        } else {
            $output->expects(exactly(2))->method('writeln');
        }
        $this->application->doRun($input, $output);
    }

    public function testPrintsErrorMessageIfParserThrowsException(): void
    {
        $input = $this->createMock(Input::class);
        $input->method('hasParameterOption')->willThrowException(
            new ParserException('Test', 'someFile.php')
        );
        $output = $this->createMock(Output::class);

        $expectedMessage = '<error>Sorry, we could not analyse your dependencies, '
            . 'because the sources contain syntax errors:' . PHP_EOL . PHP_EOL
            . 'Test in file someFile.php<error>';

        if (!extension_loaded('xdebug')) {
            $output->expects(once())->method('writeln')->with($expectedMessage);
        } else {
            $output->expects(exactly(2))->method('writeln')->withConsecutive(
                [self::XDEBUG_WARNING],
                [$expectedMessage]
            );
        }
        $this->application->doRun($input, $output);
    }

    public function testValidatesDsmInput(): void
    {
        $_SERVER['argv'] = ['', 'dsm', sys_get_temp_dir(), '--format=html'];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        assertEquals(0, $returnCode);
    }

    public function testValidatesUmlInput(): void
    {
        $_SERVER['argv'] = ['', 'uml', sys_get_temp_dir(), '--output=test.png'];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        assertEquals(0, $returnCode);
    }

    public function testValidatesMetricInput(): void
    {
        $_SERVER['argv'] = ['', 'metrics', sys_get_temp_dir()];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        assertEquals(0, $returnCode);
    }

    public function testValidatesDotInput(): void
    {
        $_SERVER['argv'] = ['', 'dot', sys_get_temp_dir()];
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $returnCode = (new Application('', '', $this->dispatcher))->doRun($input, $output);
        assertEquals(0, $returnCode);
    }

    public function testCommandWithHelpOptionProvidesHelpForDotCommand(): void
    {
        $input = new ArgvInput(['', 'dot', '--help']);
        $output = new BufferedOutput();
        $application = new Application('', '', $this->dispatcher);
        $application->add(new DotCommand($this->createMock(DotWrapper::class)));
        $application->doRun($input, $output);
        Assert::assertStringContainsString('dot [options]', $output->fetch());
    }

    public function testNoCommandWithVersionOptionWritesVersion(): void
    {
        $input = new ArgvInput(['', '--version']);
        $output = new BufferedOutput();
        (new Application('Test', '4.2.0', $this->dispatcher))->doRun($input, $output);
        assertRegExp('/\d+\.\d+\.\d+/', $output->fetch());
    }

    public function testNoCommandWithHelpOptionWritesHelp(): void
    {
        $input = new ArgvInput(['', '--help']);
        $output = new BufferedOutput();
        $application = new Application('', '', $this->dispatcher);
        $application->doRun($input, $output);
        Assert::assertStringContainsString('list [options]', $output->fetch());
    }

    public function testHelpOptionWithAnsiOptionPrintsHelp(): void
    {
        $input = new ArgvInput(['', '--help', '--ansi']);
        $output = new BufferedOutput();

        (new Application('', '', $this->dispatcher))->doRun($input, $output);
        Assert::assertStringContainsString('list [options]', $output->fetch());
    }
}
