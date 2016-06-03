<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Expr\New_ as NewNode;

class DependencyInspectionVisitor extends \PhpParser\NodeVisitorAbstract
{
    /** @var ClazzDependencies */
    private $dependencies;

    /**
     * @param ClazzDependencies $dependencies
     */
    public function __construct(ClazzDependencies $dependencies)
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
                $this->dependencies->addDependency(new Clazz($node->class->name));
            }
        }

        return;
    }

    /**
     * @return ClazzDependencies
     */
    public function dependencies() : ClazzDependencies
    {
        return $this->dependencies;
    }

    private function toFullyQualifiedClass(array $parts) : Clazz
    {
        return new Clazz(implode('.', $parts));
    }
}
