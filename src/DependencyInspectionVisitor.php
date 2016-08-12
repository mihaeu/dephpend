<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall as MethodCallNode;
use PhpParser\Node\Expr\StaticCall as StaticCallNode;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassLike as ClassLikeNode;
use PhpParser\Node\Stmt\ClassMethod as ClassMethodNode;
use PhpParser\Node\Stmt\Use_ as UseNode;
use PhpParser\NodeVisitorAbstract;

class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    /** @var DependencyPairCollection */
    private $dependencies;

    /** @var DependencyPairCollection */
    private $tempDependencies;

    /** @var Clazz */
    private $currentClass = null;

    /** @var Clazz */
    private $temporaryClass;

    public function __construct()
    {
        $this->dependencies = new DependencyPairCollection();
        $this->tempDependencies = new DependencyPairCollection();

        $this->temporaryClass = new Clazz('temporary class');
    }

    /**
     * This is called before any actual work is being done. The order in which
     * the file will be traversed is not always as expected. We therefore
     * might encounter a dependency before we actually know which class we are
     * in. To get around this issue we will set the current node to temp
     * and will update it later when we are done with the class.
     *
     * @param Node[] $nodes
     *
     * @return null|\PhpParser\Node[]|void
     */
    public function beforeTraverse(array $nodes)
    {
        $this->currentClass = $this->temporaryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassLikeNode) {
            $this->setCurrentClass($node);
            $this->addSubclassDependency($node);

            if ($node instanceof ClassNode) {
                $this->addInterfaceDependency($node);
            }
        }

        if ($node instanceof NewNode
            && $node->class instanceof FullyQualifiedNameNode) {
            $this->addInstantiationDependency($node);
        } elseif ($node instanceof ClassMethodNode) {
            $this->addInjectedDependencies($node);
        } elseif ($node instanceof UseNode) {
            $this->addUseDependency($node);
        } elseif ($node instanceof MethodCallNode
            && $node->var instanceof StaticCallNode
            && $node->var->class instanceof NameNode) {
            $this->addStaticDependency($node);
        }
    }

    /**
     * As described in beforeTraverse we are going to update the class we are
     * currently parsing for all dependencies. If we are not in class context
     * we won't add the dependencies.
     *
     * @param Node $node
     *
     * @return false|null|Node|\PhpParser\Node[]|void
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassNode) {
            // not in class context
            if ($this->currentClass->equals($this->temporaryClass)) {
                $this->tempDependencies = new DependencyPairCollection();
            }

            // by now the class should have been parsed so replace the
            // temporary class with the parsed class name
            $this->tempDependencies->each(function (DependencyPair $dependency) {
                $this->dependencies = $this->dependencies->add(new DependencyPair(
                    $this->currentClass,
                    $dependency->to()
                ));
            });
            $this->tempDependencies = new DependencyPairCollection();
        }
    }

    /**
     * @return DependencyPairCollection
     */
    public function dependencies() : DependencyPairCollection
    {
        return $this->dependencies;
    }

    /**
     * @param array $parts
     *
     * @return ClazzNamespace
     */
    private function namespaceFromParts(array $parts) : ClazzNamespace
    {
        return new ClazzNamespace(array_slice($parts, 1));
    }

    /**
     * @param ClassLikeNode $node
     */
    private function setCurrentClass(ClassLikeNode $node)
    {
        $this->currentClass = $this->clazzFromParts($node->namespacedName->parts);
    }

    /**
     * @param ClassLikeNode $node
     */
    private function addSubclassDependency(ClassLikeNode $node)
    {
        if (empty($node->extends)) {
            return;
        }

        $subClasses = $node->extends;
        if (!is_array($node->extends)) {
            $subClasses = [$subClasses];
        }

        foreach ($subClasses as $subClass) {
            $this->tempDependencies = $this->tempDependencies->add(new DependencyPair(
                $this->currentClass,
                $this->clazzFromParts($subClass->parts)
            ));
        }
    }

    /**
     * @param ClassNode $node
     */
    private function addInterfaceDependency(ClassNode $node)
    {
        foreach ($node->implements as $interfaceNode) {
            $this->tempDependencies = $this->tempDependencies->add(new DependencyPair(
                $this->currentClass,
                $this->clazzFromParts($interfaceNode->parts)
            ));
        }
    }

    /**
     * @param NewNode $node
     */
    private function addInstantiationDependency(NewNode $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(new DependencyPair(
            $this->currentClass,
            $this->clazzFromParts($node->class->parts)
        ));
    }

    /**
     * @param ClassMethodNode $node
     */
    private function addInjectedDependencies(ClassMethodNode $node)
    {
        foreach ($node->params as $param) {
            /* @var \PhpParser\Node\Param */
            if (isset($param->type, $param->type->parts)) {
                $this->tempDependencies = $this->tempDependencies->add(new DependencyPair(
                    $this->currentClass,
                    $this->clazzFromParts($param->type->parts)
                ));
            }
        }
    }

    /**
     * @param UseNode $node
     */
    private function addUseDependency(UseNode $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(new DependencyPair(
            $this->currentClass,
            $this->clazzFromParts($node->uses[0]->name->parts)
        ));
    }

    /**
     * @param MethodCallNode $node
     */
    private function addStaticDependency(MethodCallNode $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(new DependencyPair(
            $this->currentClass,
            $this->clazzFromParts($node->var->class->parts)
        ));
    }

    private function clazzFromParts(array $parts) : Clazz
    {
        return new Clazz(
            array_slice($parts, -1)[0],
            new ClazzNamespace(array_slice($parts, 0, -1))
        );
    }
}
