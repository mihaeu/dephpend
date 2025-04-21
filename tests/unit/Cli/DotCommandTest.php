<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\OS\DotWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\DotCommand
 */
class DotCommandTest extends TestCase
{
    /** @var DotCommand */
    private $dotCommand;

    /** @var InputInterface&MockObject */
    private $input;

    /** @var OutputInterface&MockObject */
    private $output;

    /** @var DotWrapper&MockObject */
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
        $this->dotWrapper->expects($this->once())->method('generate');

        $this->dotCommand->run(
            $this->input,
            $this->output
        );
    }
}
