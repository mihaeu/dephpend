<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Exceptions\ParserException;
use Mihaeu\PhpDependencies\OS\DotWrapper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Application
 * @covers \Mihaeu\PhpDependencies\Exceptions\ParserException
 */
class ApplicationTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    private const XDEBUG_WARNING = '<fg=black;bg=yellow>You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug</>';

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $_SERVER['argv'] = ['', 'text', sys_get_temp_dir()];
        $this->application = new Application('', '', $this->dispatcher);

        $this->input = $this->createMock(Input::class);
        $this->output = $this->createMock(Output::class);
        $this->output->method('getFormatter')->willReturn($this->createMock(OutputFormatter::class));
    }

    public function testWarningIfXDebugEnabled(): void
    {
        $errorOutput = $this->createMock(ErrorOutput::class);
        $this->application->setErrorOutput($errorOutput);

        // not sure how to mock this, so we test only one case, there's always one error message regarding
        // Symfony console setup, so if there's no xdebug loaded we still see one message
        if (!extension_loaded('xdebug')) {
            $errorOutput->expects($this->once())->method('writeln');
        } else {
            $errorOutput->expects($this->exactly(2))->method('writeln');
        }
        $this->application->doRun($this->input, $this->output);
    }

    public function testPrintsErrorMessageIfParserThrowsException(): void
    {
        $this->input->method('hasParameterOption')->willThrowException(
            new ParserException('Test', 'someFile.php')
        );

        $expectedMessage = '<error>Sorry, we could not analyse your dependencies, '
            . 'because the sources contain syntax errors:' . PHP_EOL . PHP_EOL
            . 'Test in file someFile.php<error>';

        $errorOutput = $this->createMock(ErrorOutput::class);
        $this->application->setErrorOutput($errorOutput);
        if (!extension_loaded('xdebug')) {
            $errorOutput->expects($this->once())->method('writeln')->with($expectedMessage);
        } else {
            $errorOutput->expects($this->exactly(2))->method('writeln')->withConsecutive(
                [self::XDEBUG_WARNING],
                [$expectedMessage]
            );
        }
        $this->application->doRun($this->input, $this->output);
    }

    public function testValidatesDsmInput(): void
    {
        $_SERVER['argv'] = ['', 'dsm', sys_get_temp_dir(), '--format=html'];

        $errorOutput = $this->createMock(ErrorOutput::class);
        $application = new Application('', '', $this->dispatcher);
        $application->setErrorOutput($errorOutput);

        $returnCode = $application->doRun($this->input, $this->output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesUmlInput(): void
    {
        $_SERVER['argv'] = ['', 'uml', sys_get_temp_dir(), '--output=test.png'];

        $errorOutput = $this->createMock(ErrorOutput::class);
        $application = new Application('', '', $this->dispatcher);
        $application->setErrorOutput($errorOutput);

        $returnCode = $application->doRun($this->input, $this->output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesMetricInput(): void
    {
        $_SERVER['argv'] = ['', 'metrics', sys_get_temp_dir()];

        $errorOutput = $this->createMock(ErrorOutput::class);
        $application = new Application('', '', $this->dispatcher);
        $application->setErrorOutput($errorOutput);

        $returnCode = $application->doRun($this->input, $this->output);
        $this->assertEquals(0, $returnCode);
    }

    public function testValidatesDotInput(): void
    {
        $_SERVER['argv'] = ['', 'dot', sys_get_temp_dir()];

        $errorOutput = $this->createMock(ErrorOutput::class);
        $application = new Application('', '', $this->dispatcher);
        $application->setErrorOutput($errorOutput);

        $returnCode = $application->doRun($this->input, $this->output);
        $this->assertEquals(0, $returnCode);
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
        $this->assertMatchesRegularExpression('/\d+\.\d+\.\d+/', $output->fetch());
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
