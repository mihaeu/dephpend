<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\DotWrapper;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Mihaeu\PhpDependencies\Util\Functional;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\DotCommand
 */
class DotCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var DotCommand */
    private $dotCommand;

    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var DotWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $dotWrapper;

    public function setUp()
    {
        $this->dotWrapper = $this->createMock(DotWrapper::class);
        $this->dotCommand = new DotCommand(
            new DependencyMap(),
            Functional::id(),
            $this->dotWrapper
        );
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }
    public function testGenerateDot()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'output' => '/tmp/test.png',
            'keep' => false,
            'internals' => false,
            'filter-namespace' => null,
            'depth' => 0
        ]);
        $this->dotWrapper->expects($this->once())->method('generate');

        $this->dotCommand->run(
            $this->input,
            $this->output
        );
    }
}
