<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\ClazzLike;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup as AttributeGroupNode;
use PhpParser\Node\Expr\ClassConstFetch as FetchClassConstantNode;
use PhpParser\Node\Expr\Instanceof_ as InstanceofNode;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\StaticCall as StaticCallNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier as IdentifierNode;
use PhpParser\Node\IntersectionType as IntersectionTypeNode;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\NullableType as NullableTypeNode;
use PhpParser\Node\Stmt\Catch_ as CatchNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassLike as ClassLikeNode;
use PhpParser\Node\Stmt\ClassMethod as ClassMethodNode;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\Node\Stmt\TraitUse as UseTraitNode;
use PhpParser\Node\Stmt\Use_ as UseNode;
use PhpParser\Node\Stmt\Enum_ as EnumNode;
use PhpParser\Node\Stmt\Property as PropertyNode;
use PhpParser\Node\UnionType as UnionNodeType;
use PhpParser\NodeVisitorAbstract;

/**
 * @phpstan-type SubclassedNode ClassNode|InterfaceNode
 */
class DependencyInspectionVisitor extends NodeVisitorAbstract
{
    private DependencyMap $dependencies;

    private DependencySet $tempDependencies;

    private ?ClazzLike $currentClass;

    private DependencyFactory $dependencyFactory;
    
    private array $variableValues = [];
    
    private ?Node $currentScope = null;
    /**
     * PHP primitive types to ignore when parsing docblocks
     */
    private const PHP_PRIMITIVES = [
        'string', 'int', 'float', 'bool', 'boolean', 'array',
        'object', 'callable', 'resource', 'null', 'mixed',
        'void', 'iterable', 'self', 'static', 'parent', 'false', 'true',
        'double', 'integer', 'numeric',
    ];

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

            $this->extractDocBlockDependencies($node);
        } elseif ($node instanceof PropertyNode) {
            $this->addType($node->type);
        } elseif ($node instanceof NewNode) {
            if ($node->class instanceof NameNode) {
                $this->addName($node->class);
            }
        } elseif ($node instanceof ClassMethodNode) {
            $this->addInjectedDependencies($node);
            $this->addReturnType($node);
            
            $this->extractDocBlockDependencies($node);
        } elseif ($node instanceof UseNode) {
            foreach ($node->uses as $use) {
                $this->addName($use->name);
            }
        } elseif ($node instanceof StaticCallNode) {
            if ($node->class instanceof NameNode) {
                $this->addName($node->class);
            } 
        } elseif ($node instanceof UseTraitNode) {
            foreach ($node->traits as $trait) {
                $this->addName($trait);
            }
        } elseif ($node instanceof InstanceofNode) {
            // todo: handle when class is a variable
            if ($node->class instanceof NameNode) {
                $this->addName($node->class);
            }
        } elseif ($node instanceof FetchClassConstantNode
            && !$node->class instanceof Node\Expr\Variable
            && !$node->class instanceof Node\Expr\ArrayDimFetch
            && !$node->class instanceof Node\Expr\PropertyFetch
            && !$node->class instanceof Node\Expr\MethodCall) {
            $this->addName($node->class);
        } elseif ($node instanceof CatchNode) {
            foreach ($node->types as $name) {
                $this->addType($name);
            }
        } elseif ($node instanceof AttributeGroupNode) {
            foreach ($node->attrs as $attribute) {
                $this->addName($attribute->name);
            }
        } elseif ($node instanceof UnionNodeType) {
            foreach ($node->types as $type) {
                $this->addType($type);
            }
        } elseif ($node instanceof IntersectionTypeNode) {
            foreach ($node->types as $type) {
                $this->addType($type);
            }
        }

        return null;
    }

    public function addName(NameNode|IdentifierNode|null $name): void
    {
        if ($name instanceof IdentifierNode || $name === null) {
            return;
        }

        if ($name->isSpecialClassName()) {
            return;
        }

        if (! $name instanceof FullyQualifiedNameNode) {
            return;
        }

        if ($name instanceof VariableNode) {
            $name->getType();
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
     * @return false|null|Node|\PhpParser\Node[]
     */
    public function leaveNode(Node $node): bool|null|Node|array
    {
        if ($node instanceof ClassLikeNode) {
            $this->leaveCurrentClass();
        }

        return null;
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
        $this->variableValues = [];
        $this->currentScope = null;
    }

    /**
     * @return DependencyMap
     */
    public function dependencies(): DependencyMap
    {
        return $this->dependencies;
    }

    private function setCurrentClass(ClassLikeNode $node): void
    {
        if (!isset($node->namespacedName)) {
            return;
        }

        if ($node instanceof InterfaceNode) {
            $this->currentClass = $this->dependencyFactory->createInterfazeFromStringArray($node->namespacedName->getParts());
        } elseif ($node instanceof TraitNode) {
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

    private function leaveCurrentClass(): void
    {
        if ($this->currentClass === null) {
            return;
        }

        $this->dependencies = $this->dependencies->addSet(
            $this->currentClass,
            $this->tempDependencies
        );
        $this->tempDependencies = new DependencySet();
        $this->currentClass = null;
    }

    /**
     * @param SubclassedNode $node
     */
    private function addParentDependency(ClassLikeNode $node): void
    {
        // interfaces EXTEND other interfaces, they don't implement them,
        // so if the node is an interface, then this could contain
        // multiple dependencies
        $extendedClasses = is_array($node->extends)
            ? $node->extends
            : [$node->extends];
        foreach ($extendedClasses as $extendedClass) {
            $this->addName($extendedClass);
        }
    }

    /**
     * @param EnumNode|ClassNode $node
     */
    private function addImplementedInterfaceDependency(EnumNode|ClassNode $node): void
    {
        foreach ($node->implements as $interfaceNode) {
            $this->addName($interfaceNode);
        }
    }

    /**
     * @param ClassMethodNode $node
     */
    private function addInjectedDependencies(ClassMethodNode $node): void
    {
        foreach ($node->params as $param) {
            $this->addType($param->type);
        }
    }

    private function addType(NameNode|IdentifierNode|UnionNodeType|IntersectionTypeNode|NullableTypeNode|null $type): void
    {
        if ($type instanceof NameNode) {
            $this->addName($type);
        } elseif ($type instanceof UnionNodeType || $type instanceof IntersectionTypeNode) {
            $this->addRecursiveType($type);
        } elseif ($type instanceof NullableTypeNode) {
            $this->addName($type->type);
        } else {
            $this->addName($type);
        }
    }

    /**
     * @phpstan-assert-if-true ClassNode|InterfaceNode $node
     */
    private function isSubclass(ClassLikeNode $node): bool
    {
        if ($node instanceof InterfaceNode || $node instanceof ClassNode) {
            return !empty($node->extends);
        }

        return false;
    }

    private function addReturnType(FunctionLike $node): void
    {
        $this->addType($node->getReturnType());
    }

    private function addRecursiveType(UnionNodeType|IntersectionTypeNode|IdentifierNode|NameNode $type): void
    {
        if ($type instanceof NameNode || $type instanceof IdentifierNode) {
            $this->addName($type);
            return;
        }

        foreach ($type->types as $type) {
            $this->addRecursiveType($type);
        }
    }

    /**
     * Extract class dependencies from PHPDoc comments.
     */
    private function extractDocBlockDependencies(Node $node): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        $docText = $docComment->getText();
        $this->extractDocBlockTypeHints($docText, '@param');
        $this->extractDocBlockTypeHints($docText, '@return');
        $this->extractDocBlockTypeHints($docText, '@throws');
        $this->extractDocBlockTypeHints($docText, '@var');
        $this->extractDocBlockTypeHints($docText, '@property');
        $this->extractDocBlockTypeHints($docText, '@property-read');
        $this->extractDocBlockTypeHints($docText, '@property-write');
    }

    private function extractDocBlockTypeHints(string $docText, string $tag): void
    {
        // Match tags with type annotations, handling various formats like:
        // @param array<Namespace\ClassName> $param
        // @param Namespace\ClassName[]|null $param
        // @param ?Namespace\ClassName $param
        // @param Namespace\ClassName<K,V> $param
        // @return \Namespace\ClassName
        // @throws Namespace\Exception1|Namespace\Exception2
        // @var Collection<int, Namespace\ClassName>
        $pattern = '/' . preg_quote($tag) . '\s+([^\s$]+)/i';
        preg_match_all($pattern, $docText, $matches);
        
        if (empty($matches[1])) {
            return;
        }

        foreach ($matches[1] as $typeHint) {
            // Handle nullable type with leading question mark
            if (strpos($typeHint, '?') === 0) {
                $typeHint = substr($typeHint, 1);
            }
            
            // Handle intersection types (Type1&Type2)
            $intersectionTypes = explode('&', $typeHint);
            
            foreach ($intersectionTypes as $intersectionType) {
                // Handle union types (Type1|Type2)
                $unionTypes = explode('|', $intersectionType);

                foreach ($unionTypes as $type) {
                    $this->processDocBlockType($type);
                }
            }
        }
    }

    /**
     * Process a single type from a PHPDoc comment
     */
    private function processDocBlockType(string $type): void
    {
        // Strip any variable name that might follow the type
        $type = trim($type);

        if (empty($type)) {
            return;
        }
        // Strip array notation variants (Type[] or Type[][])
        $type = preg_replace('/(\[\])+$/', '', $type);

        // Remove nullable type indicator
        if (strpos($type, '?') === 0) {
            $type = substr($type, 1);
        }

        // Remove leading backslash for fully qualified names
        if (strpos($type, '\\') === 0) {
            $type = substr($type, 1);
        }

        // Normalize again after all stripping
        $type = trim($type);
        
        // Skip primitive types and null
        if ($this->isPrimitiveType($type) || $type === 'null') {
            return;
        }

        if (!empty($type)) {
            // Only add fully qualified names
            if (strpos($type, '\\')) {
                $nameNode = new FullyQualifiedNameNode($type);
                $this->addName($nameNode);
            }
        }
    }

    /**
     * Check if a type is a PHP primitive type
     */
    private function isPrimitiveType(string $type): bool
    {
        // Remove array notation if present
        $type = preg_replace('/(\[\])+$/', '', $type);
        
        // Remove nullable type indicator
        if (strpos($type, '?') === 0) {
            $type = substr($type, 1);
        }
        
        // Remove leading backslash for fully qualified names
        if (strpos($type, '\\') === 0) {
            $type = substr($type, 1);
        }
        
        // Handle generic collection types like array<int>
        if (preg_match('/^(array|iterable|collection|list|generator)</', strtolower($type))) {
            return true;
        }
        
        return in_array(strtolower($type), self::PHP_PRIMITIVES, true);
    }
}
