<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Util\DI;
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

    public function setUp()
    {
        $this->dI = $this->createMock(DI::class);
        $this->application = new Application('', '', $this->dI);
    }

    public function testSetMemoryLimit()
    {
        $input = $this->createMock(Input::class);
        $input->method('hasOption')->willReturn(true);
        $input->method('getOption')->willReturn('1234M');
        $input->method('hasParameterOption')->willReturn(false);
        $output = $this->createMock(Output::class);
        $this->application->doRun($input, $output);
        $this->assertEquals('1234M', ini_get('memory_limit'));
    }

    public function testWarningIfXDebugEnabled()
    {
        $input = $this->createMock(Input::class);
        $input->method('hasParameterOption')->willReturn(false);
        $output = $this->createMock(Output::class);

        // not sure how to mock this, so we test only one case
        if (!extension_loaded('xdebug')) {
            $output->expects($this->never())->method('writeln');
        } else {
            $output->expects($this->once())->method('writeln');
        }
        $this->application->doRun($input, $output);
    }
}
