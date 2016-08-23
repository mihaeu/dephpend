<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Application
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testSetMemoryLimit()
    {
        $app = new Application();
        $input = $this->createMock(Input::class);
        $input->method('hasOption')->willReturn(true);
        $input->method('getOption')->willReturn('1234M');
        $output = $this->createMock(Output::class);
        $app->doRun($input, $output);
        $this->assertEquals('1234M', ini_get('memory_limit'));
    }

    public function testWarningIfXDebugEnabled()
    {
        $app = new Application();
        $input = $this->createMock(Input::class);
        $output = $this->createMock(Output::class);
        $output->expects($this->once())->method('writeln');
        $app->doRun($input, $output);
    }
}
