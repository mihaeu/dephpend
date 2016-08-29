<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Clazz;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Namespaze;
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
        $dependencies = DependencyHelper::convert('
            A\\a\\1\\ClassA --> B\\a\\1\\ClassB
            A\\a\\1\\ClassA --> C\\a\\1\\ClassC
            B\\a\\1\\ClassB --> C\\a\\1\\ClassC
        ');
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOption')->willReturn(false, 0);
        $this->input->method('getOptions')->willReturn(['internals' => false, 'filter-namespace' => null, 'depth' => 0]);

        $this->output->expects($this->once())
            ->method('writeln')
            ->with(
                'A\\a\\1\\ClassA --> B\\a\\1\\ClassB'.PHP_EOL
                .'A\\a\\1\\ClassA --> C\\a\\1\\ClassC'.PHP_EOL
                .'B\\a\\1\\ClassB --> C\\a\\1\\ClassC'
            );
        $this->textCommand->run($this->input, $this->output);
    }

    public function testPrintsOnlyNamespacedDependencies()
    {
        $dependencies = DependencyHelper::convert('
            NamespaceA\\A --> NamespaceB\\B
            NamespaceA\\A --> NamespaceC\\C
            NamespaceB\\B --> NamespaceC\\C
        ');
        $this->analyser->method('analyse')->willReturn($dependencies);

        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn(['internals' => false, 'filter-namespace' => null, 'depth' => 1]);

        $this->output->expects($this->once())
            ->method('writeln')
            ->with(
                'NamespaceA --> NamespaceB'.PHP_EOL
                .'NamespaceA --> NamespaceC'.PHP_EOL
                .'NamespaceB --> NamespaceC'
            );
        $this->textCommand->run($this->input, $this->output);
    }
}
