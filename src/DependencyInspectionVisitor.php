<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassMethod as ClassMethodNode;
use PhpParser\Node\Stmt\Use_ as UseNode;
use PhpParser\NodeVisitorAbstract;

class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    /** @var DependencyCollection */
    private $dependencies;

    /** @var DependencyCollection */
    private $tempDependencies;

    /** @var Clazz */
    private $currentClass = null;

    public function __construct()
    {
        $this->dependencies = new DependencyCollection();
        $this->tempDependencies = new DependencyCollection();
    }

    public function beforeTraverse(array $nodes)
    {
        $this->currentClass = new Clazz('temp');
    }

    public function afterTraverse(array $nodes)
    {
        // by now the class should have been parsed so replace the
        // temporary class with the parsed class name
        $this->tempDependencies->each(function (Dependency $dependency) {
            $this->dependencies = $this->dependencies->add(new Dependency(
                $this->currentClass,
                $dependency->to()
            ));
        });
        $this->tempDependencies = new DependencyCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassNode) {
            $this->currentClass = new Clazz($this->toFullyQualifiedName($node->namespacedName->parts));
            if ($node->extends !== null) {
                $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($this->toFullyQualifiedName($node->extends->parts))
                ));
            }
            foreach ($node->implements as $interfaceNode) {
                $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($this->toFullyQualifiedName($interfaceNode->parts))
                ));
            }
        }

        if ($node instanceof NewNode) {
            if ($node->class instanceof FullyQualifiedNameNode) {
                $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($this->toFullyQualifiedName($node->class->parts))
                ));
            } elseif ($node->class instanceof VariableNode) {
                $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($node->class->name)
                ));
            }
        } elseif ($node instanceof ClassMethodNode) {
            foreach ($node->params as $param) { /* @var \PhpParser\Node\Param */
                if (isset($param->type, $param->type->parts)) {
                    $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                        $this->currentClass,
                        new Clazz($this->toFullyQualifiedName($param->type->parts))
                    ));
                }
            }
        } elseif ($node instanceof UseNode) {
            $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                $this->currentClass,
                new Clazz($this->toFullyQualifiedName($node->uses[0]->name->parts))
            ));
        }
    }

    /**
     * @return DependencyCollection
     */
    public function dependencies() : DependencyCollection
    {
        return $this->dependencies;
    }

    /**
     * @param array $parts
     *
     * @return string
     */
    private function toFullyQualifiedName(array $parts) : string
    {
        return implode('.', $parts);
    }
}
