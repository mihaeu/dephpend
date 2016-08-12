<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Clazz;
use Mihaeu\PhpDependencies\ClazzNamespace;
use Mihaeu\PhpDependencies\DependencyPair;
use Mihaeu\PhpDependencies\DependencyPairCollection;
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
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(
                new Clazz('ClassA', new ClazzNamespace(['A', 'a', '1'])),
                new Clazz('ClassB', new ClazzNamespace(['B', 'a', '1']))))
            ->add(new DependencyPair(
                new Clazz('ClassA', new ClazzNamespace(['A', 'a', '1'])),
                new Clazz('ClassC', new ClazzNamespace(['C', 'a', '1']))))
            ->add(new DependencyPair(
                new Clazz('ClassB', new ClazzNamespace(['B', 'a', '1'])),
                new Clazz('ClassC', new ClazzNamespace(['C', 'a', '1']))));
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOption')->willReturn(false, 0);

        $this->output->expects($this->exactly(3))
            ->method('writeln')
            ->withConsecutive(
            ['A\\a\\1\\ClassA --> B\\a\\1\\ClassB'],
            ['A\\a\\1\\ClassA --> C\\a\\1\\ClassC'],
            ['B\\a\\1\\ClassB --> C\\a\\1\\ClassC']
        );
        $this->textCommand->run($this->input, $this->output);
    }

    public function testPrintsOnlyNamespacedDependencies()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(
                new Clazz('A', new ClazzNamespace(['NamespaceA'])),
                new Clazz('B', new ClazzNamespace(['NamespaceB'])))
            )->add(new DependencyPair(
                new Clazz('A', new ClazzNamespace(['NamespaceA'])),
                new Clazz('C', new ClazzNamespace(['NamespaceC'])))
            )->add(new DependencyPair(
                new Clazz('B', new ClazzNamespace(['NamespaceB'])),
                new Clazz('C', new ClazzNamespace(['NamespaceC'])))
            );
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOption')->willReturn(false, 1);

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
