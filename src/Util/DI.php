<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Mihaeu\PhpDependencies\Analyser\DependencyInspectionVisitor;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class DI
{
    /** @var array */
    private $internals;

    /**
     * @param array $internals
     */
    public function __construct(array $internals)
    {
        $this->internals = $internals;
    }

    public function dependencyFilter() : DependencyFilter
    {
        return new DependencyFilter($this->internals);
    }

    /**
     * @return PhpFileFinder
     */
    public function phpFileFinder() : PhpFileFinder
    {
        return new PhpFileFinder();
    }

    /**
     * @return Parser
     */
    public function parser() : Parser
    {
        return new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
    }

    /**
     * @return DependencyFactory
     */
    public function dependencyFactory() : DependencyFactory
    {
        return new DependencyFactory();
    }

    /**
     * @return StaticAnalyser
     */
    public function staticAnalyser() : StaticAnalyser
    {
        return  new StaticAnalyser(
            new NodeTraverser(),
            new DependencyInspectionVisitor(
                $this->dependencyFactory()
            )
        );
    }

    public function xDebugFunctionTraceAnalyser() : XDebugFunctionTraceAnalyser
    {
        return new XDebugFunctionTraceAnalyser();
    }
}
