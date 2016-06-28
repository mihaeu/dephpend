<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;
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
            $this->setCurrentClass($node);
            $this->addSubclassDependency($node);
            $this->addInterfaceDependency($node);
        }

        if ($node instanceof NewNode
            && $node->class instanceof FullyQualifiedNameNode) {
            $this->addInstantiationDependency($node);
        } elseif ($node instanceof ClassMethodNode) {
            $this->addInjectedDependencies($node);
        } elseif ($node instanceof UseNode) {
            $this->addUseDependency($node);
        } elseif ($node instanceof Node\Expr\MethodCall
            && $node->var instanceof Node\Expr\StaticCall
            && $node->var->class instanceof Node\Name) {
            $this->addStaticDependency($node);
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

    /**
     * @param Node $node
     */
    private function setCurrentClass(Node $node)
    {
        $this->currentClass = new Clazz($this->toFullyQualifiedName($node->namespacedName->parts));
    }

    /**
     * @param Node $node
     */
    private function addSubclassDependency(Node $node)
    {
        if ($node->extends !== null) {
            $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                $this->currentClass,
                new Clazz($this->toFullyQualifiedName($node->extends->parts))
            ));
        }
    }

    /**
     * @param Node $node
     */
    private function addInterfaceDependency(Node $node)
    {
        foreach ($node->implements as $interfaceNode) {
            $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                $this->currentClass,
                new Clazz($this->toFullyQualifiedName($interfaceNode->parts))
            ));
        }
    }

    /**
     * @param Node $node
     */
    private function addInstantiationDependency(Node $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(new Dependency(
            $this->currentClass,
            new Clazz($this->toFullyQualifiedName($node->class->parts))
        ));
    }

    /**
     * @param Node $node
     */
    private function addInjectedDependencies(Node $node)
    {
        foreach ($node->params as $param) {
            /* @var \PhpParser\Node\Param */
            if (isset($param->type, $param->type->parts)) {
                $this->tempDependencies = $this->tempDependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($this->toFullyQualifiedName($param->type->parts))
                ));
            }
        }
    }

    /**
     * @param Node $node
     */
    private function addUseDependency(Node $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(new Dependency(
            $this->currentClass,
            new Clazz($this->toFullyQualifiedName($node->uses[0]->name->parts))
        ));
    }

    /**
     * @param Node $node
     */
    private function addStaticDependency(Node $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(new Dependency(
            $this->currentClass,
            new Clazz($this->toFullyQualifiedName($node->var->class->parts))
        ));
    }
}
