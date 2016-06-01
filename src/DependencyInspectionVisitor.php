<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Stmt\ClassMethod as ClassMethodNode;
use PhpParser\Node\Expr\New_ as NewNode;

class DependencyInspectionVisitor extends \PhpParser\NodeVisitorAbstract
{
    /** @var ClassDependencies */
    private $dependencies;

    /**
     * @param ClassDependencies $dependencies
     */
    public function __construct(ClassDependencies $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof NewNode) {
            if ($node->class instanceof FullyQualifiedNameNode) {
                $this->dependencies->addDependency($this->toFullyQualifiedClass($node->class->parts));
            } elseif ($node->class instanceof VariableNode) {
                $this->dependencies->addDependency($this->toFullyQualifiedClass($node->class->name));
            }
        } elseif ($node instanceof ClassMethodNode) {
        }

        return;
    }

    /**
     * @return ClassDependencies
     */
    public function dependencies() : ClassDependencies
    {
        return $this->dependencies;
    }

    private function toFullyQualifiedClass(array $parts) : Clazz
    {
        return new Clazz(implode('.', $parts));
    }
}
