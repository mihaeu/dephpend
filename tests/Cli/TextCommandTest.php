<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Clazz;
use Mihaeu\PhpDependencies\Dependency;
use Mihaeu\PhpDependencies\DependencyCollection;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\TextCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class TextCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var TextCommand */
    private $textCommand;

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

    public function setUp()
    {
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->parser = $this->createMock(Parser::class);
        $this->analyser = $this->createMock(Analyser::class);
        $this->textCommand = new TextCommand(
            $this->phpFileFinder,
            $this->parser,
            $this->analyser
        );
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testPrintsDependencies()
    {
        $dependencies = (new DependencyCollection())
            ->add(new Dependency(new Clazz('A'), new Clazz('B')))
            ->add(new Dependency(new Clazz('A'), new Clazz('C')))
            ->add(new Dependency(new Clazz('B'), new Clazz('C')));
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->input->method('getArgument')->willReturn(sys_get_temp_dir());
        $this->input->method('getOption')->willReturn(false);

        $this->output->expects($this->exactly(3))
            ->method('writeln')
            ->withConsecutive(
            ['A --> B'],
            ['A --> C'],
            ['B --> C']
        );
        $this->textCommand->run($this->input, $this->output);
    }

    public function testPrintsOnlyNamespacedDependencies()
    {
        $dependencies = (new DependencyCollection())
            ->add(new Dependency(new Clazz('NamespaceA.A'), new Clazz('NamespaceB.B')))
            ->add(new Dependency(new Clazz('NamespaceA.A'), new Clazz('NamespaceC.C')))
            ->add(new Dependency(new Clazz('NamespaceB.B'), new Clazz('NamespaceC.C')));
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->input->method('getArgument')->willReturn(sys_get_temp_dir());
        $this->input->method('getOption')->willReturn(false, true);

        $this->output->expects($this->exactly(3))
            ->method('writeln')
            ->withConsecutive(
                ['NamespaceA --> NamespaceB'],
                ['NamespaceA --> NamespaceC'],
                ['NamespaceB --> NamespaceC']
            );
        $this->textCommand->run($this->input, $this->output);
    }
}
