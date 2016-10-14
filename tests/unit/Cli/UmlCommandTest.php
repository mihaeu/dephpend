<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Mihaeu\PhpDependencies\Util\Functional;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\UmlCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class UmlCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var UmlCommand */
    private $umlCommand;
    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var PhpFileFinder|\PHPUnit_Framework_MockObject_MockObject */
    private $phpFileFinder;

    /** @var Parser|\PHPUnit_Framework_MockObject_MockObject */
    private $parser;

    /** @var StaticAnalyser|\PHPUnit_Framework_MockObject_MockObject */
    private $analyser;

    /** @var PlantUmlWrapper|\PHPUnit_Framework_MockObject_MockObject */
    private $plantUmlWrapper;

    /** @var DependencyFilter|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyFilter;

    public function setUp()
    {
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->phpFileFinder->method('find')->willReturn(new PhpFileSet());
        $this->parser = $this->createMock(Parser::class);
        $this->analyser = $this->createMock(StaticAnalyser::class);
        $this->plantUmlWrapper = $this->createMock(PlantUmlWrapper::class);
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);
        $this->umlCommand = new UmlCommand(
            $this->phpFileFinder,
            $this->parser,
            $this->analyser,
            $this->dependencyFilter,
            $this->plantUmlWrapper
        );
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testCheckIfSourceExists()
    {
        $this->input->method('getArgument')->willReturn(['/tsdfsfsfs']);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File/Directory does not exist or is not readable.');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testOutputHasToBeDefined()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Output not defined (use "help" for more information).');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testChecksIfDestinationIsWritable()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn(['output' => '/sdfsdfsd']);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination is not writable.');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testGenerateUml()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'output' => '/tmp/test.png',
            'keep-uml' => false,
            'internals' => false,
            'filter-namespace' => null,
            'depth' => 0
        ]);
        $this->dependencyFilter->method('filterByOptions')->willReturn(new DependencyMap());

        $this->plantUmlWrapper->expects($this->once())->method('generate');

        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }

    public function testAcceptsOnlyAllowedFormats()
    {
        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn([
            'output' => sys_get_temp_dir().'/test.bmp'
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Output format is not allowed (png)');
        $this->umlCommand->run(
            $this->input,
            $this->output
        );
    }
}
