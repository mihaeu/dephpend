<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFile;
use Mihaeu\PhpDependencies\OS\PhpFileSet;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class StaticAnalyser
{
    /** @var NodeTraverser */
    protected $nodeTraverser;

    /** @var DependencyInspectionVisitor */
    protected $dependencyInspectionVisitor;

    /** @var Parser */
    protected $parser;

    /**
     * @param NodeTraverser               $nodeTraverser
     * @param DependencyInspectionVisitor $dependencyInspectionVisitor
     */
    public function __construct(
        NodeTraverser $nodeTraverser,
        DependencyInspectionVisitor $dependencyInspectionVisitor,
        Parser $parser
    ) {
        $this->dependencyInspectionVisitor = $dependencyInspectionVisitor;

        $this->nodeTraverser = $nodeTraverser;
        $this->nodeTraverser->addVisitor(new NameResolver());
        $this->nodeTraverser->addVisitor($this->dependencyInspectionVisitor);

        $this->parser = $parser;
    }

    public function analyse(PhpFileSet $files) : DependencyMap
    {
        $files->each(function (PhpFile $file) {
            $this->nodeTraverser->traverse(
                $this->parser->parse($file)
            );
        });

        return $this->dependencyInspectionVisitor->dependencies();
    }
}
