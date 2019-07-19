<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch as FetchClassConstantNode;
use PhpParser\Node\Expr\Instanceof_ as InstanceofNode;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticCall as StaticCallNode;
use PhpParser\Node\Name;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Catch_ as CatchNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassLike as ClassLikeNode;
use PhpParser\Node\Stmt\ClassMethod as ClassMethodNode;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\Node\Stmt\TraitUse as UseTraitNode;
use PhpParser\Node\Stmt\Use_ as UseNode;
use PhpParser\NodeVisitorAbstract;

class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    /** @var DependencyMap */
    private $dependencies;

    /** @var DependencySet */
    private $tempDependencies;

    /** @var Clazz */
    private $currentClass;

    /** @var DependencyFactory */
    private $dependencyFactory;

    /**
     * @param DependencyFactory $dependencyFactory
     */
    public function __construct(DependencyFactory $dependencyFactory)
    {
        $this->dependencyFactory = $dependencyFactory;
        $this->dependencies = new DependencyMap();
        $this->tempDependencies = new DependencySet();
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof ClassLikeNode) {
            $this->setCurrentClass($node);

            if ($this->isSubclass($node)) {
                $this->addParentDependency($node);
            }

            if ($node instanceof ClassNode) {
                $this->addImplementedInterfaceDependency($node);
            }
        } elseif ($node instanceof NewNode
            && $node->class instanceof NameNode) {
            $this->addName($node->class);
        } elseif ($node instanceof ClassMethodNode) {
            $this->addInjectedDependencies($node);
            $this->addReturnType($node);
        } elseif ($node instanceof UseNode) {
            foreach ($node->uses as $use) {
                $this->addName($use->name);
            }
        } elseif ($node instanceof StaticCallNode
            && $node->class instanceof NameNode) {
            $this->addStaticDependency($node);
        } elseif ($node instanceof UseTraitNode) {
            foreach ($node->traits as $trait) {
                $this->addName($trait);
            }
        } elseif ($node instanceof InstanceofNode) {
            $this->addInstanceofDependency($node);
        } elseif ($node instanceof FetchClassConstantNode
            && !$node->class instanceof Node\Expr\Variable) {
            $this->addName($node->class);
        } elseif ($node instanceof CatchNode) {
            foreach ($node->types as $name) {
                $this->addName($name);
            }
        }
    }

    public function addName(Name $name)
    {
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($name->parts)
        );
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
        if (!$node instanceof ClassLikeNode) {
            return null;
        }

        // not in class context
        if ($this->currentClass === null) {
            $this->tempDependencies = new DependencySet();
            return;
        }

        // by now the class should have been parsed so replace the
        // temporary class with the parsed class name

        $this->dependencies = $this->dependencies->addSet(
            $this->currentClass,
            $this->tempDependencies
        );
        $this->tempDependencies = new DependencySet();
        $this->currentClass = null;
    }

    /**
     * Reset state when parsing a new AST.
     *
     * @param Node[] $nodes
     *
     * @return null|Node[]|void
     */
    public function beforeTraverse(array $nodes)
    {
        $this->tempDependencies = new DependencySet();
        $this->currentClass = null;
    }

    /**
     * @return DependencyMap
     */
    public function dependencies() : DependencyMap
    {
        return $this->dependencies;
    }

    /**
     * @param ClassLikeNode $node
     */
    private function setCurrentClass(ClassLikeNode $node)
    {
        if (!isset($node->namespacedName)) {
            return;
        }

        if ($node instanceof InterfaceNode) {
            $this->currentClass = $this->dependencyFactory->createInterfazeFromStringArray($node->namespacedName->parts);
        // @codeCoverageIgnoreStart
        } elseif ($node instanceof TraitNode) {
            // @codeCoverageIgnoreEnd
            $this->currentClass = $this->dependencyFactory->createTraitFromStringArray($node->namespacedName->parts);
        } else {
            $this->currentClass = $node->isAbstract()
                ? $this->dependencyFactory->createAbstractClazzFromStringArray($node->namespacedName->parts)
                : $this->dependencyFactory->createClazzFromStringArray($node->namespacedName->parts);
        }
    }

    /**
     * @param ClassLikeNode $node
     */
    private function addParentDependency(ClassLikeNode $node)
    {
        // interfaces EXTEND other interfaces, they don't implement them,
        // so if the node is an interface, then this could contain
        // multiple dependencies
        $extendedClasses = is_array($node->extends)
            ? $node->extends
            : [$node->extends];
        foreach ($extendedClasses as $extendedClass) {
            $this->tempDependencies = $this->tempDependencies->add(
                $this->dependencyFactory->createClazzFromStringArray($extendedClass->parts)
            );
        }
    }

    /**
     * @param ClassNode $node
     */
    private function addImplementedInterfaceDependency(ClassNode $node)
    {
        foreach ($node->implements as $interfaceNode) {
            $this->tempDependencies = $this->tempDependencies->add(
                $this->dependencyFactory->createClazzFromStringArray($interfaceNode->parts)
            );
        }
    }

    /**
     * @param ClassMethodNode $node
     */
    private function addInjectedDependencies(ClassMethodNode $node)
    {
        foreach ($node->params as $param) {
            /* @var Param */
            if (isset($param->type, $param->type->parts)) {
                $this->tempDependencies = $this->tempDependencies->add(
                    $this->dependencyFactory->createClazzFromStringArray($param->type->parts)
                );
            }
        }
    }

    /**
     * @param StaticCallNode $node
     */
    private function addStaticDependency(StaticCall $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($node->class->parts)
        );
    }

    /**
     * @param ClassLikeNode $node
     *
     * @return bool
     */
    private function isSubclass(ClassLikeNode $node)
    {
        return !empty($node->extends);
    }

    /**
     * @param Node $node
     */
    protected function addReturnType(Node $node)
    {
        if ($node->returnType instanceof NameNode) {
            $this->tempDependencies = $this->tempDependencies->add(
                $this->dependencyFactory->createClazzFromStringArray($node->returnType->parts)
            );
        }
    }

    /**
     * @param Node $node
     */
    private function addInstanceofDependency(Node $node)
    {
        if ($node->class instanceof NameNode) {
            $this->tempDependencies = $this->tempDependencies->add(
                $this->dependencyFactory->createClazzFromStringArray($node->class->parts)
            );
        }
    }
}
