<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class DI
{
    /** @var Analyser */
    private $analyser;

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
     * @return Analyser
     */
    public function analyser(bool $withUnderscoreSupport = false) : Analyser
    {
        $clazzFactory = $withUnderscoreSupport
            ? new UnderscoreClazzFactory()
            : new ClazzFactory();
        $dependencyInspectionVisitor = new DependencyInspectionVisitor($clazzFactory);

        return  new Analyser(new NodeTraverser(), $dependencyInspectionVisitor);
    }
}
