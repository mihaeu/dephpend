<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\UmlCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class UmlCommandTest extends TestCase
{
    /** @var UmlCommand */
    private $umlCommand;

    /** @var InputInterface&MockObject */
    private $input;

    /** @var OutputInterface&MockObject */
    private $output;

    /** @var PlantUmlWrapper&MockObject */
    private $plantUmlWrapper;

    protected function setUp(): void
    {
        $this->plantUmlWrapper = $this->createMock(PlantUmlWrapper::class);
        $this->umlCommand = new UmlCommand($this->plantUmlWrapper);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testCheckIfSourceExists(): void
    {
        $this->input->method('getArgument')->willReturn(['/tsdfsfsfs']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File/Directory does not exist or is not readable.');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testOutputHasToBeDefined(): void
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn(['output' => null]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Output not defined (use "help" for more information).');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testChecksIfDestinationIsWritable(): void
    {
        $tempDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . rand() . time();
        mkdir($tempDirectory, 0444);

        $this->input->method('getArgument')->willReturn([$tempDirectory]);
        $this->input->method('getOptions')->willReturn(['output' => $tempDirectory . '/does_not_exist.png']);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination is not writable.');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testGenerateUml(): void
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'output' => '/tmp/test.png',
            'keep-uml' => false,
            'internals' => false,
            'filter-namespace' => null,
            'depth' => 0
        ]);
        $this->plantUmlWrapper->expects($this->once())->method('generate');

        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testAcceptsOnlyAllowedFormats(): void
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'output' => sys_get_temp_dir().'/test.bmp'
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Output format is not allowed (png)');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }
}
