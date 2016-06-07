<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\NodeVisitorAbstract;

class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    /** @var ClazzDependencies */
    private $dependencies;

    /** @var string */
    private $currentClass = null;

    public function __construct()
    {
        $this->dependencies = new ClazzDependencies();
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassNode) {
            $this->currentClass = new Clazz($this->toFullyQualifiedName($node->namespacedName->parts));
        }

        if ($node instanceof NewNode && $this->currentClass !== null) {
            if ($node->class instanceof FullyQualifiedNameNode) {
                $this->dependencies = $this->dependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($this->toFullyQualifiedName($node->class->parts))
                ));
            } elseif ($node->class instanceof VariableNode) {
                $this->dependencies = $this->dependencies->add(new Dependency(
                    $this->currentClass,
                    new Clazz($node->class->name)
                ));
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
