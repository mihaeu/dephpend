<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Util\Functional;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\TextCommand
 * @covers Mihaeu\PhpDependencies\Cli\BaseCommand
 */
class TextCommandTest extends TestCase
{
    /** @var TextCommand */
    private $textCommand;

    /** @var InputInterface&MockObject */
    private $input;

    /** @var OutputInterface&MockObject */
    private $output;

    /** @var DependencyFilter&MockObject */
    private $dependencyFilter;

    protected function setUp(): void
    {
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testPrintsDependencies(): void
    {
        $dependencies = DependencyHelper::map('
            A\\a\\1\\ClassA --> B\\a\\1\\ClassB
            A\\a\\1\\ClassA --> C\\a\\1\\ClassC
            B\\a\\1\\ClassB --> C\\a\\1\\ClassC
        ');
        $command = new TextCommand();
        $command->setDependencies($dependencies);
        $command->setPostProcessors(Functional::id());

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
        $command->run($this->input, $this->output);
    }

    public function testPrintsOnlyNamespacedDependencies(): void
    {
        $dependencies = DependencyHelper::map('
            NamespaceA --> NamespaceB
            NamespaceA --> NamespaceC
            NamespaceB --> NamespaceC
        ');
        $command = new TextCommand();
        $command->setDependencies($dependencies);

        $this->input->method('getArgument')->willReturn([sys_get_temp_dir()]);
        $this->input->method('getOptions')->willReturn(['internals' => false, 'filter-namespace' => null, 'depth' => 1]);

        $this->output->expects($this->once())
            ->method('writeln')
            ->with(
                'NamespaceA --> NamespaceB'.PHP_EOL
                .'NamespaceA --> NamespaceC'.PHP_EOL
                .'NamespaceB --> NamespaceC'
            );
        $command->run($this->input, $this->output);
    }
}
