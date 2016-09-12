<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\DependencyInspectionVisitor;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\UnderscoreDependencyFactory;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class DI
{
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
     * @param bool $withUnderscoreSupport
     *
     * @return DependencyFactory
     */
    public function dependencyFactory(bool $withUnderscoreSupport = false) : DependencyFactory
    {
        return $withUnderscoreSupport
            ? new UnderscoreDependencyFactory()
            : new DependencyFactory();
    }

    /**
     * @param bool $withUnderscoreSupport
     * @return Analyser
     */
    public function analyser(bool $withUnderscoreSupport = false) : Analyser
    {
        return  new Analyser(
            new NodeTraverser(),
            new DependencyInspectionVisitor(
                $this->dependencyFactory($withUnderscoreSupport)
            )
        );
    }
}
