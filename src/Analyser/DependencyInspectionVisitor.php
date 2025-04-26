<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\ClazzLike;
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
use PhpParser\Node\Stmt\Enum_ as EnumNode;
use PhpParser\Node\UnionType;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;

/**
 * @phpstan-type SubclassedNode ClassNode|InterfaceNode
 */
class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    private DependencyMap $dependencies;

    private DependencySet $tempDependencies;

    private ?ClazzLike $currentClass;

    private DependencyFactory $dependencyFactory;

    public function __construct(DependencyFactory $dependencyFactory)
    {
        $this->dependencyFactory = $dependencyFactory;
        $this->dependencies = new DependencyMap();
        $this->tempDependencies = new DependencySet();
    }

    /**
     * {@inheritdoc}
     *
     * @return array<Node>|int|Node|null
     */
    public function enterNode(Node $node): array|int|Node|null
    {
        if ($node instanceof ClassLikeNode) {
            $this->setCurrentClass($node);

            if ($this->isSubclass($node)) {
                $this->addParentDependency($node);
            }

            if ($node instanceof EnumNode || $node instanceof ClassNode) {
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
            && !$node->class instanceof Node\Expr\Variable
            && !$node->class instanceof Node\Expr\ArrayDimFetch
            && !$node->class instanceof Node\Expr\PropertyFetch
            && !$node->class instanceof Node\Expr\MethodCall) {
            $this->addName($node->class);
        } elseif ($node instanceof CatchNode) {
            foreach ($node->types as $name) {
                $this->addName($name);
            }
        } elseif ($node instanceof AttributeGroup) {
            foreach ($node->attrs as $attribute) {
                $this->addName($attribute->name);
            }
        } elseif ($node instanceof UnionType) {
            foreach ($node->types as $type) {
                if ($type instanceof NameNode) {
                    $this->addName($type);
                }
            }
        } elseif ($node instanceof IntersectionType) {
            foreach ($node->types as $type) {
                if ($type instanceof NameNode) {
                    $this->addName($type);
                }
            }
        }

        return null;
    }

    public function addName(Name $name)
    {
        if (count($name->getParts()) <= 0) {
            return;
        }
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($name->getParts())
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
    public function dependencies(): DependencyMap
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
            $this->currentClass = $this->dependencyFactory->createInterfazeFromStringArray($node->namespacedName->getParts());
            // @codeCoverageIgnoreStart
        } elseif ($node instanceof TraitNode) {
            // @codeCoverageIgnoreEnd
            $this->currentClass = $this->dependencyFactory->createTraitFromStringArray($node->namespacedName->getParts());
        } elseif ($node instanceof ClassNode) {
            $this->currentClass = $node->isAbstract()
                ? $this->dependencyFactory->createAbstractClazzFromStringArray($node->namespacedName->getParts())
                : $this->dependencyFactory->createClazzFromStringArray($node->namespacedName->getParts());
        } elseif ($node instanceof EnumNode) {
            $this->currentClass = $this->dependencyFactory->createClazzFromStringArray($node->namespacedName->getParts());
        } else {
            $this->currentClass = $this->dependencyFactory->createClazzFromStringArray($node->namespacedName->getParts());
        }
    }

    /**
     * @param SubclassedNode $node
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
                $this->dependencyFactory->createClazzFromStringArray($extendedClass->getParts())
            );
        }
    }

    /**
     * @param EnumNode|ClassNode $node
     */
    private function addImplementedInterfaceDependency(EnumNode|ClassNode $node)
    {
        foreach ($node->implements as $interfaceNode) {
            $this->addName($interfaceNode);
        }
    }

    /**
     * @param ClassMethodNode $node
     */
    private function addInjectedDependencies(ClassMethodNode $node)
    {
        foreach ($node->params as $param) {
            /* @var Param */
            if ($param->type instanceof NameNode) {
                $this->addName($param->type);
            } elseif (($param->type instanceof UnionType || $param->type instanceof IntersectionType)) {
                foreach ($param->type->types as $type) {
                    if ($type instanceof NameNode) {
                        $this->addName($type);
                    }
                }
            } elseif ($param->type instanceof NullableType) {
                if ($param->type->type instanceof NameNode) {
                    $this->addName($param->type->type);
                }
            }
        }
    }

    /**
     * @param StaticCallNode $node
     */
    private function addStaticDependency(StaticCall $node)
    {
        $this->tempDependencies = $this->tempDependencies->add(
            $this->dependencyFactory->createClazzFromStringArray($node->class->getParts())
        );
    }

    private function isSubclass(ClassLikeNode $node): bool
    {
        if ($node instanceof InterfaceNode || $node instanceof ClassNode) {
            return !empty($node->extends);
        }

        return false;
    }

    protected function addReturnType(FunctionLike $node)
    {
        $returnType = $node->getReturnType();
        if ($returnType instanceof NameNode) {
            $this->addName($returnType);
        } elseif (($returnType instanceof UnionType || $returnType instanceof IntersectionType)) {
            foreach ($returnType->types as $type) {
                if ($type instanceof NameNode) {
                    $this->addName($type);
                }
            }
        } elseif ($returnType instanceof NullableType) {
            $this->addName($returnType->type);
        }
    }

    private function addInstanceofDependency(InstanceofNode $node)
    {
        if ($node->class instanceof NameNode) {
            $this->addName($node->class);
        }
    }
}
