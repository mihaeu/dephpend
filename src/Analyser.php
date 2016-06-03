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
     * @param NodeTraverser $nodeTraverser
     */
    public function __construct(NodeTraverser $nodeTraverser)
    {
        $this->nodeTraverser = $nodeTraverser;
        $this->nodeTraverser->addVisitor(new NameResolver());
    }

    public function analyse(Ast $ast) : array
    {
        return $ast->mapToArray(function (PhpFile $file, array $nodes) {
            $dependencies = $this->changeDependencyInspector($file);
            $this->nodeTraverser->traverse($nodes);

            return $dependencies->dependencies();
        });
    }

    /**
     * @param PhpFile $file
     *
     * @return ClazzDependencies
     */
    private function changeDependencyInspector(PhpFile $file) : ClazzDependencies
    {
        if ($this->dependencyInspectionVisitor !== null) {
            $this->nodeTraverser->removeVisitor($this->dependencyInspectionVisitor);
        }

        $dependencies = new ClazzDependencies(new Clazz($file->file()->getBasename()));
        $this->dependencyInspectionVisitor = new DependencyInspectionVisitor($dependencies);
        $this->nodeTraverser->addVisitor(new DependencyInspectionVisitor($dependencies));

        return $dependencies;
    }
}
