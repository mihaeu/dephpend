<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

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
}
