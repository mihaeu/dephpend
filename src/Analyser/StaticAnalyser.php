<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\ParserException;
use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class StaticAnalyser
{
    public function __construct(
        private NodeTraverser $nodeTraverser,
        private DependencyInspectionVisitor $dependencyInspectionVisitor,
        private Parser $parser
    ) {
        $this->nodeTraverser->addVisitor(new NameResolver());
        $this->nodeTraverser->addVisitor($this->dependencyInspectionVisitor);
    }

    public function analyse(PhpFileSet $files): DependencyMap
    {
        $files->each(function (PhpFile $file) {
            try {
                $this->nodeTraverser->traverse(
                    $this->parser->parse($file->code())
                );
            } catch (Error $e) {
                throw new ParserException($e->getMessage(), $file->file()->getPathname());
            }
        });

        return $this->dependencyInspectionVisitor->dependencies();
    }
}
