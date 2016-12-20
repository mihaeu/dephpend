<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use PhpParser\NodeTraverser;

class CachedStaticAnalyzer extends StaticAnalyser
{
    /** @var Cache */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(
        Cache $cache,
        NodeTraverser $nodeTraverser,
        DependencyInspectionVisitor $dependencyInspectionVisitor,
        Parser $parser
    ) {
        $this->cache = $cache;

        parent::__construct($nodeTraverser, $dependencyInspectionVisitor, $parser);
    }

    public function analyse(PhpFileSet $files): DependencyMap
    {
        $files->each(function (PhpFile $file) {
            //            if () {
//
//            }

            $this->nodeTraverser->traverse(
                $this->parser->parse($file)
            );
        });

        return $this->dependencyInspectionVisitor->dependencies();
    }
}
