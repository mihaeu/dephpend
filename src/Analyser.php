<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class Analyser
{
    /** @var NodeTraverser */
    private $nodeTraverser;

    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor = null;

    /**
     * @param NodeTraverser               $nodeTraverser
     * @param DependencyInspectionVisitor $dependencyInspectionVisitor
     */
    public function __construct(NodeTraverser $nodeTraverser, DependencyInspectionVisitor $dependencyInspectionVisitor)
    {
        $this->dependencyInspectionVisitor = $dependencyInspectionVisitor;

        $this->nodeTraverser = $nodeTraverser;
        $this->nodeTraverser->addVisitor(new NameResolver());
        $this->nodeTraverser->addVisitor($this->dependencyInspectionVisitor);
    }

    /**
     * @param Ast $ast
     *
     * @return ClazzDependencies[]
     */
    public function analyse(Ast $ast) : array
    {
        $ast->each(function (PhpFile $file, array $nodes) {
            $this->nodeTraverser->traverse($nodes);
        });

        return $this->dependencyInspectionVisitor->dependencies();
    }
}
