<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\OS\DotWrapper;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\DotCommand
 */
class DotCommandTest extends TestCase
{
    /** @var DotCommand */
    private $dotCommand;

    /** @var InputInterface|PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var DotWrapper|PHPUnit_Framework_MockObject_MockObject */
    private $dotWrapper;

    protected function setUp(): void
    {
        $this->dotWrapper = $this->createMock(DotWrapper::class);
        $this->dotCommand = new DotCommand($this->dotWrapper);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }
    public function testGenerateDot(): void
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'output' => '/tmp/test.png',
            'keep' => false,
            'internals' => false,
            'filter-namespace' => null,
            'depth' => 0
        ]);
        $this->dotWrapper->expects(once())->method('generate');

        $this->dotCommand->run(
            $this->input,
            $this->output
        );
    }
}
