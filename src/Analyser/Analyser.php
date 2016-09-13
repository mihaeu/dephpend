<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class Analyser
{
    /** @var NodeTraverser */
    private $nodeTraverser;

    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor;

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
     * @return DependencyMap
     */
    public function analyse(Ast $ast) : DependencyMap
    {
        $ast->each(function (array $nodes) {
            $this->nodeTraverser->traverse($nodes);
        });

        return $this->dependencyInspectionVisitor->dependencies();
    }
}
