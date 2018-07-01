<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\DsmCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class DsmCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var DsmCommand */
    private $dsmCommand;

    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var DependencyFilter|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyFilter;

    /** @var DependencyStructureMatrixHtmlFormatter|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyStructureMatrixFormatter;

    public function setUp()
    {
        $this->dependencyStructureMatrixFormatter = $this->createMock(DependencyStructureMatrixHtmlFormatter::class);
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);
        $this->dsmCommand = new DsmCommand($this->dependencyStructureMatrixFormatter);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testPassesDependenciesToFormatter()
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

        $this->dependencyStructureMatrixFormatter->expects(once())->method('format')->with($dependencies);
        $this->dsmCommand->run($this->input, $this->output);
    }

    public function testDoesNotAllowOtherFormats()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn(['format' => 'tiff']);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Output format is not allowed (html)');
        $this->dsmCommand->run($this->input, $this->output);
    }
}
