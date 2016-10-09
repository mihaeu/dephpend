<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\DsmCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class DsmCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var DsmCommand */
    private $dsmCommand;

    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var PhpFileFinder|\PHPUnit_Framework_MockObject_MockObject */
    private $phpFileFinder;

    /** @var Parser|\PHPUnit_Framework_MockObject_MockObject */
    private $parser;

    /** @var Analyser|\PHPUnit_Framework_MockObject_MockObject */
    private $analyser;

    /** @var DependencyFilter|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyFilter;

    /** @var DependencyStructureMatrixHtmlFormatter|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyStructureMatrixFormatter;

    public function setUp()
    {
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->phpFileFinder->method('find')->willReturn(new PhpFileSet());
        $this->parser = $this->createMock(Parser::class);
        $this->analyser = $this->createMock(Analyser::class);
        $this->dependencyStructureMatrixFormatter = $this->createMock(DependencyStructureMatrixHtmlFormatter::class);
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);
        $this->dsmCommand = new DsmCommand(
            $this->phpFileFinder,
            $this->parser,
            $this->analyser,
            $this->dependencyFilter,
            $this->dependencyStructureMatrixFormatter
        );
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testHandsDependenciesToFormatter()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'format' => 'html',
            'internals' => false,
            'filter-namespace' => null,
            'depth' => 0,
            'no-classes' => true
        ]);

        $dependencies = DependencyHelper::map('A --> B');
        $this->analyser->method('analyse')->willReturn($dependencies);
        $this->dependencyFilter->method('filterByOptions')->willReturn($dependencies);

        $this->dependencyStructureMatrixFormatter->expects($this->once())->method('format')->with($dependencies, $dependencies);
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
