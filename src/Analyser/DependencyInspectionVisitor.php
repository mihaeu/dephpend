<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\ClazzLike;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch as FetchClassConstantNode;
use PhpParser\Node\Expr\Instanceof_ as InstanceofNode;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticCall as StaticCallNode;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
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
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Property as PropertyNode;

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

    private array $templateTypes = [];
    private array $addedTemplateConstraints = [];

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
        // Set our current scope to the node we are currently parsing
        // This is a naive implementation and will need to be improved
        // to support more complex cases, such as closures and anonymous functions.
        // It's just used now to handle creating objects from variables.
        if ($node instanceof FunctionLike) {
            $this->currentScope = $node;
            $this->variableValues[spl_object_id($node)] = [];
        }

        if ($node instanceof Assign && $node->var instanceof Variable) {
            if ($node->expr instanceof String_) {
                $varName = $node->var->name;
                $stringValue = $this->normalizeTypeIdentifier($node->expr->value);
                if ($this->currentScope !== null) {
                    $scopeId = spl_object_id($this->currentScope);
                    $this->variableValues[$scopeId][$varName] = $stringValue;
                }
            } elseif ($node->expr instanceof FetchClassConstantNode &&
                    $node->expr->class instanceof NameNode &&
                    $node->expr->name instanceof Identifier &&
                    $node->expr->name->name === 'class') {
                $varName = $node->var->name;
                if ($this->currentScope !== null) {
                    $className = $node->expr->class->toString();
                    $scopeId = spl_object_id($this->currentScope);
                    $this->variableValues[$scopeId][$varName] = $className;
                }
            }
        }

        if ($node instanceof ClassLikeNode) {
            if ($node->getDocComment() instanceof Doc) {
                $docText = $node->getDocComment()->getText();
                $this->extractTemplateTypes($docText);
            }
            $this->setCurrentClass($node);

            if ($this->isSubclass($node)) {
                $this->addParentDependency($node);
            }

            if ($node instanceof EnumNode || $node instanceof ClassNode) {
                $this->addImplementedInterfaceDependency($node);
            }
        } elseif ($node instanceof PropertyNode) {
            // Handle native property type declarations
            if ($node->type instanceof NameNode) {
                $this->addName($node->type);
            } elseif ($node->type instanceof UnionType || $node->type instanceof IntersectionType) {
                foreach ($node->type->types as $type) {
                    if ($type instanceof NameNode) {
                        $this->addName($type);
                    } elseif ($type instanceof NullableType && $type->type instanceof NameNode) {
                        $this->addName($type->type);
                    }
                }
            } elseif ($node->type instanceof NullableType) {
                if ($node->type->type instanceof NameNode) {
                    $this->addName($node->type->type);
                }
            }
        } elseif ($node instanceof NewNode) {
            if ($node->class instanceof NameNode) {
                $this->addName($node->class);
            } elseif ($node->class instanceof Variable &&
                    is_string($node->class->name) &&
                    $this->currentScope !== null) {
                $scopeId = spl_object_id($this->currentScope);
                if (isset($this->variableValues[$scopeId][$node->class->name])) {
                    $className = $this->variableValues[$scopeId][$node->class->name];
                    // No need to normalize here as values are already normalized when stored
                    $nameNode = new Name($className);
                    $this->addName($nameNode);
                }
            }
        } elseif ($node instanceof ClassMethodNode) {
            $this->addInjectedDependencies($node);
            $this->addReturnType($node);
            
            // Extract dependencies from method docblocks
            $this->extractDocBlockDependencies($node);
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

        if ($node->getDocComment() instanceof Doc) {
            $docText = $node->getDocComment()->getText();
            $this->extractDocBlockTypeHints($docText, '@var');
            $this->extractDocBlockTypeHints($docText, '@param');
            $this->extractDocBlockTypeHints($docText, '@return');
            $this->extractDocBlockTypeHints($docText, '@throws');
            $this->extractDocBlockTypeHints($docText, '@property');
            $this->extractDocBlockTypeHints($docText, '@property-read');
            $this->extractDocBlockTypeHints($docText, '@property-write');
        }

        return null;
    }

    public function addName(Name $name): void
    {
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
        if ($node instanceof FunctionLike && $node === $this->currentScope) {
            unset($this->variableValues[spl_object_id($node)]);
            $this->currentScope = null;
        }
    
        if ($node instanceof ClassLikeNode) {
            $this->templateTypes = [];
            $this->addedTemplateConstraints = [];
        }
    
        if (!$node instanceof ClassLikeNode) {
            return null;
        }

        // not in class context
        if ($this->currentClass === null) {
            $this->tempDependencies = new DependencySet();
            return null;
        }

        // by now the class should have been parsed so replace the
        // temporary class with the parsed class name

        $this->dependencies = $this->dependencies->addSet(
            $this->currentClass,
            $this->tempDependencies
        );
        $this->tempDependencies = new DependencySet();
        $this->currentClass = null;

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
        $this->templateTypes = [];
        $this->addedTemplateConstraints = [];
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

    private function addStaticDependency(StaticCall $node): void
    {
        $this->addName($node->class);
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

    private function addInstanceofDependency(InstanceofNode $node): void
    {
        if ($node->class instanceof NameNode) {
            $this->addName($node->class);
        }
    }
    
    /**
     * Normalize type identifier to ensure proper capitalization.
     * This applies to classes, interfaces, traits, and enums.
     *
     * In PHP, class-like identifiers should start with uppercase letters,
     * and we need to ensure consistent capitalization for dependency tracking.
     */
    private function normalizeTypeIdentifier(string $identifier): string
    {
        if (strpos($identifier, '\\') !== false) {
            // Handle namespaced identifiers
            $parts = explode('\\', $identifier);
            foreach ($parts as &$part) {
                $part = ucfirst($part);
            }
            return implode('\\', $parts);
        }
        
        // Simple identifier
        return ucfirst($identifier);
    }
    
    /**
     * Extract class dependencies from PHPDoc comments.
     */
    private function extractDocBlockDependencies(Node $node): void
    {
        $docComment = $node->getDocComment();
        if (null === $docComment) {
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
        if (($dollarPos = strpos($type, '$')) !== false) {
            $type = substr($type, 0, $dollarPos);
        }
        // Normalize type
        $type = trim($type);
        // Skip empty types
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
        // Skip template types, but add their constraint as a dependency if present
        if (array_key_exists($type, $this->templateTypes)) {
            $constraint = $this->templateTypes[$type];
            if ($constraint && !$this->isPrimitiveType($constraint)) {
                if (!isset($this->addedTemplateConstraints[$constraint])) {
                    $normalized = $this->normalizeTypeIdentifier($constraint);
                    $nameNode = new Name($normalized);
                    $this->addName($nameNode);
                    $this->addedTemplateConstraints[$constraint] = true;
                }
            }
            return;
        }
        
        // Skip primitive types and null
        if ($this->isPrimitiveType($type) || $type === 'null') {
            return;
        }
        
        // Handle fully qualified class names starting with '\'
        if (strpos($type, '\\') === 0) {
            $type = substr($type, 1);
        }
        
        if (!empty($type)) {
            $nameNode = new Name($type);
            $this->addName($nameNode);
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

    private function extractTemplateTypes(string $docText): void
    {
        // Regex to match '* @template T of B' in docblocks
        $pattern = '/^\s*\*\s*@template\s+(\w+)(?:\s+of\s+([^\s*]+))?/mi';
        preg_match_all($pattern, $docText, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $template = $match[1];
            $this->templateTypes[$template] = isset($match[2]) ? $match[2] : null;
            // If there is a constraint, add it as a dependency for the current class
            if (isset($match[2]) && !$this->isPrimitiveType($match[2])) {
                $normalized = $this->normalizeTypeIdentifier($match[2]);
                $this->addName(new Name($normalized));
            }
        }
    }
}
