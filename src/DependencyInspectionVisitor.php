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
    /** @var ClazzDependencies[] */
    private $dependencies;

    /** @var string */
    private $currentClass = 'GLOBAL';

    public function __construct()
    {
        $this->dependencies = [
            'GLOBAL' => new ClazzDependencies(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassNode) {
            $this->currentClass = $this->toFullyQualifiedName($node->namespacedName->parts);
            $this->dependencies[$this->currentClass] = new ClazzDependencies(new Clazz($this->currentClass));
        }

        if ($node instanceof NewNode) {
            if ($node->class instanceof FullyQualifiedNameNode) {
                $this->dependencies[$this->currentClass]->addDependency(new Clazz($this->toFullyQualifiedName($node->class->parts)));
            } elseif ($node->class instanceof VariableNode) {
                $this->dependencies[$this->currentClass]->addDependency(new Clazz($node->class->name));
            }
        }

        return;
    }

    /**
     * @return array
     */
    public function dependencies() : array
    {
        return $this->dependencies;
    }

    private function toFullyQualifiedName(array $parts) : string
    {
        return implode('.', $parts);
    }
}
