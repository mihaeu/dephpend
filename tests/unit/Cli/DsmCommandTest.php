<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Exception;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\DsmCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class DsmCommandTest extends TestCase
{
    /** @var DsmCommand */
    private $dsmCommand;

    /** @var InputInterface&MockObject */
    private $input;

    /** @var OutputInterface&MockObject */
    private $output;

    /** @var DependencyFilter&MockObject */
    private $dependencyFilter;

    /** @var DependencyStructureMatrixHtmlFormatter&MockObject */
    private $dependencyStructureMatrixFormatter;

    protected function setUp(): void
    {
        $this->dependencyStructureMatrixFormatter = $this->createMock(DependencyStructureMatrixHtmlFormatter::class);
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);
        $this->dsmCommand = new DsmCommand($this->dependencyStructureMatrixFormatter);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testPassesDependenciesToFormatter(): void
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'format' => 'html',
            'internals' => false,
            'filter-namespace' => null,
            'depth' => 0,
            'no-classes' => true
        ]);
        $this->dsmCommand = new DsmCommand($this->dependencyStructureMatrixFormatter);
        $dependencies = DependencyHelper::map('A --> B');
        $this->dsmCommand->setDependencies($dependencies);

        $this->dependencyStructureMatrixFormatter->expects($this->once())->method('format')->with($dependencies);
        $this->dsmCommand->run($this->input, $this->output);
    }

    public function testDoesNotAllowOtherFormats(): void
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn(['format' => 'tiff']);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Output format is not allowed (html)');
        $this->dsmCommand->run($this->input, $this->output);
    }
}
