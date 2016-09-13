<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall as MethodCallNode;
use PhpParser\Node\Expr\StaticCall as StaticCallNode;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Expr\New_ as NewNode;
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
            && $node->class instanceof FullyQualifiedNameNode) {
            $this->addInstantiationDependency($node);
            // WEIRD BUG CAUSING XDEBUG TO NOT COVER ELSEIF ONLY ELSE IF
            // @codeCoverageIgnoreStart
        } elseif ($node instanceof ClassMethodNode) {
            // @codeCoverageIgnoreEnd
            $this->addInjectedDependencies($node);
            // WEIRD BUG CAUSING XDEBUG TO NOT COVER ELSEIF ONLY ELSE IF
            // @codeCoverageIgnoreStart
        } elseif ($node instanceof UseNode) {
            // @codeCoverageIgnoreEnd
            $this->addUseDependency($node);
        } elseif ($node instanceof MethodCallNode
            && $node->var instanceof StaticCallNode
            && $node->var->class instanceof NameNode) {
            $this->addStaticDependency($node);
            // WEIRD BUG CAUSING XDEBUG TO NOT COVER ELSEIF ONLY ELSE IF
            // @codeCoverageIgnoreStart
        } elseif ($node instanceof UseTraitNode) {
            // @codeCoverageIgnoreEnd
            $this->addUseTraitDependency($node);
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
        if ($node instanceof ClassLikeNode) {
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
        return null;
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
        if ($node instanceof InterfaceNode) {
            $this->currentClass = $this->dependencyFactory->createInterfazeFromStringArray($node->namespacedName->parts);
        } elseif ($node instanceof TraitNode) {
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
     * @param NewNode $node
     */
    private function addInstantiationDependency(NewNode $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($node->class->parts)
        );
    }

    /**
     * @param ClassMethodNode $node
     */
    private function addInjectedDependencies(ClassMethodNode $node)
    {
        foreach ($node->params as $param) {
            /* @var \PhpParser\Node\Param */
            if (isset($param->type, $param->type->parts)) {
                $this->tempDependencies = $this->tempDependencies->add(
                    $this->dependencyFactory->createClazzFromStringArray($param->type->parts)
                );
            }
        }
    }

    /**
     * @param UseNode $node
     */
    private function addUseDependency(UseNode $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($node->uses[0]->name->parts)
        );
    }

    /**
     * @param MethodCallNode $node
     */
    private function addStaticDependency(MethodCallNode $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($node->var->class->parts)
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
    private function addUseTraitDependency(Node $node)
    {
        foreach ($node->traits as $trait) {
            $this->tempDependencies = $this->tempDependencies->add(
                $this->dependencyFactory->createTraitFromStringArray($trait->parts)
            );
        }
    }
}
