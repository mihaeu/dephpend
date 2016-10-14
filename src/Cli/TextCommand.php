<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TextCommand extends BaseCommand
{
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        StaticAnalyser $analyser,
        DependencyFilter $dependencyFilter)
    {
        parent::__construct('text', $phpFileFinder, $parser, $analyser, $dependencyFilter);
    }

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Generate a Dependency Structure Matrix of your dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureSourcesAreReadable($input->getArgument('source'));

        $dependencies = $this->detectDependencies($input->getArgument('source'));
//        $dependencies = new DependencyMap();
//        $dependencies = (new XDebugFunctionTraceAnalyser())->analyse(new \SplFileInfo('/tmp/trace.2955610183.xt'))->reduce($dependencies, function (DependencyMap $map, Dependency $from, Dependency $to) {
//            return $map->add($from, $to);
//        });
        $options = $input->getOptions();
        $output->writeln($this->dependencyFilter->filterByOptions($dependencies, $options)->toString());
    }
}
