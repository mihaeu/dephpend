<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Clazz;
use Mihaeu\PhpDependencies\DependencyPair;
use Mihaeu\PhpDependencies\DependencyPairCollection;
use Mihaeu\PhpDependencies\DependencyStructureMatrixHtmlFormatter;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
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

    /** @var DependencyStructureMatrixHtmlFormatter|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyStructureMatrixFormatter;

    public function setUp()
    {
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->parser = $this->createMock(Parser::class);
        $this->analyser = $this->createMock(Analyser::class);
        $this->dependencyStructureMatrixFormatter = $this->createMock(DependencyStructureMatrixHtmlFormatter::class);
        $this->dsmCommand = new DsmCommand(
            $this->phpFileFinder,
            $this->parser,
            $this->analyser,
            $this->dependencyStructureMatrixFormatter
        );
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testHandsDependenciesToFormatter()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOption')->willReturn('html', true, 0);
        $this->input->method('getOptions')->willReturn(['internals' => false, 'vendor' => null, 'depth' => 0]);

        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('A'), new Clazz('B')));
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->dependencyStructureMatrixFormatter->expects($this->once())->method('format')->with($dependencies);
        $this->dsmCommand->run($this->input, $this->output);
    }

    public function testDoesNotAllowOtherFormats()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOption')->willReturn('tiff');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Output format is not allowed (html)');
        $this->dsmCommand->run($this->input, $this->output);
    }
}
