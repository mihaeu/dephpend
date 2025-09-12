<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\AbstractClazz;
use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\Interfaze;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;
use Mihaeu\PhpDependencies\Dependencies\Trait_;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_ as EnumNode;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_ as UseNode;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Attribute;
use PhpParser\Node\UnionType;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Identifier;
use PhpParser\Comment\Doc;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The Dependency Inspection is where all the magic happens,
 * as such it is extra important to make sure it's all covered
 * with tests.
 *
 * The problem here is that the underlying architecture (PhpParser)
 * makes the setup a bit more difficult.
 *
 * Basically you want to ensure that a dependency will be picked up (or not).
 * The way to do that is to enter a class like node and within that node
 * (meaning before leaving that node) you enter and leave another node.
 *
 * As an example:
 *
 *  - enter the Class node TestClassA
 *  - enter a Method node with a typed parameter OtherClass $other
 *  - leave the Method node
 *  - leave the Class node
 *  - verify that TestClassA --> OtherClass was detected
 *
 */
#[CoversClass(DependencyInspectionVisitor::class)]
class DependencyInspectionVisitorTest extends TestCase
{
    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor;

    protected function setUp(): void
    {
        $this->dependencyInspectionVisitor = new DependencyInspectionVisitor(new DependencyFactory());
        $this->dependencyInspectionVisitor->beforeTraverse([]);
    }

    /**
     * Helper method which makes testing more convenient. This does not check
     * for the right order (both from --> to or to --> from would be valid).
     * It only ensures that the dependency was picked up.
     *
     * @param DependencyMap $dependencies
     * @param Dependency               $otherDependency
     *
     * @return bool
     */
    private function dependenciesContain(DependencyMap $dependencies, Dependency $otherDependency) : bool
    {
        return $dependencies->any(function (Dependency $from, Dependency $to) use ($otherDependency) {
            return $from->equals($otherDependency)
                || $to->equals($otherDependency);
        });
    }

    /**
     * Helper method which creates a normal namespaced Class node and enters it.
     * Before any assertions you have to leave that node. This is mostly
     * useful when testing if the to-dependency is picked up and the from-
     * dependency is not important.
     *
     * @return ClassNode
     */
    private function createAndEnterCurrentClassNode(): ClassNode
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new Name('SomeNamespace\\SomeClass');
        $this->dependencyInspectionVisitor->enterNode($node);

        return $node;
    }

    /**
     * Helper method to create a named class context.
     *
     * @param string $className
     * @return ClassNode
     */
    private function createAndEnterNamedClassNode(string $className): ClassNode
    {
        $node = new ClassNode($className);
        $node->namespacedName = new Name($className);
        $this->dependencyInspectionVisitor->enterNode($node);

        return $node;
    }

    /**
     * Helper to check a specific directional dependency
     */
    private function hasDependency(DependencyMap $dependencies, string $from, string $to): bool
    {
        $found = false;
        $dependencies->each(function (Dependency $fromDep, Dependency $toDep) use (&$found, $from, $to) {
            if ($fromDep->toString() === $from && $toDep->toString() === $to) {
                $found = true;
            }
        });
        return $found;
    }

    /**
     * Helper method which adds a random dependency when testing if the from-
     * dependency is picked up and the to-dependency is not important.
     */
    private function addRandomDependency(): void
    {
        $newNode = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($newNode);
        $this->dependencyInspectionVisitor->leaveNode($newNode);
    }

    public function testDetectsExplicitNewCreation(): void
    {
        $node = $this->createAndEnterCurrentClassNode();

        $newNode = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($newNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain($this->dependencyInspectionVisitor->dependencies(), new Clazz('TestDep')));
    }

    public function testDetectsExtendedClasses(): void
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new Name('SomeNamespace\\SomeClass');
        $node->extends = new Name(['A', 'a', '1', 'ClassA']);

        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ClassA', new Namespaze(['A', 'a', '1']))
        ));
    }

    public function testDetectsWhenInterfacesImplementMultipleInterfaces(): void
    {
        $node = new InterfaceNode('SomeInterface', ['extends' => [new Name('A\\a\\1\\ClassA'), new Name('B\\b\\2\\ClassB')]]);
        $node->namespacedName = new Name('SomeNamespace\\SomeInterface');
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ClassA', new Namespaze(['A', 'a', '1']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ClassB', new Namespaze(['B', 'b', '2']))
        ));
    }

    public function testDetectsAbstractClasses(): void
    {
        $node = new ClassNode('Test', ['type' => Modifiers::ABSTRACT]);
        $node->namespacedName = new Name('A\\Test');

        $this->dependencyInspectionVisitor->enterNode($node);

        $this->addRandomDependency();

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new AbstractClazz('Test', new Namespaze(['A']))
        ));
    }

    public function testDetectsInterfaces(): void
    {
        $node = new InterfaceNode('Test');
        $node->namespacedName = new Name('A\\Test');
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->addRandomDependency();

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Interfaze('Test', new Namespaze(['A']))
        ));
    }

    public function testDetectsImplementedInterfaces(): void
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new Name('SomeNamespace\\SomeClass');

        $interfaceOneNode = new Name('A\\B\\InterfaceOne');
        $interfaceTwoNode = new Name('C\\D\\InterfaceTwo');
        $node->implements = [$interfaceOneNode, $interfaceTwoNode];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('InterfaceOne', new Namespaze(['A', 'B']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('InterfaceTwo', new Namespaze(['C', 'D']))
        ));
    }

    public function testIgnoresInnerClassesWithoutName(): void
    {
        $node = new ClassNode(null);
        $this->dependencyInspectionVisitor->enterNode($node);
        $this->dependencyInspectionVisitor->leaveNode($node);

        $this->assertEmpty($this->dependencyInspectionVisitor->dependencies());
    }

    public function testDetectsDependenciesFromMethodArguments(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $paramOne = new Param(new Variable('one'), null, new Name(['A\\B\\DependencyOne']));
        $paramTwo = new Param(new Variable('two'), null, new Name(['A\\B\\DependencyTwo']));
        $methodNode->params = [
            $paramOne,
            $paramTwo,
        ];

        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('DependencyOne', new Namespaze(['A', 'B']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('DependencyTwo', new Namespaze(['A', 'B']))
        ));
    }

    public function testDetectsUseNodes(): void
    {
        $this->addNodeToAst(
            new UseNode(
                [new Node\Stmt\UseUse(new Name(['A', 'a', '1', 'Test']))]
            )
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Test', new Namespaze(['A', 'a', '1']))
        ));
    }

    public function testReturnType(): void
    {
        $this->addNodeToAst(
            new ClassMethod('anyMethod', ['returnType' => new Name(['Namespace', 'Test'])])
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Test', new Namespaze(['Namespace']))
        ));
    }

    public function testDetectsCallsOnStaticClasses(): void
    {
        $node = $this->createAndEnterCurrentClassNode();

        $staticCall = new StaticCall(new FullyQualifiedNameNode('A\\a\\1\\Singleton'), 'Singleton');
        $this->dependencyInspectionVisitor->enterNode($staticCall);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Singleton', new Namespaze(['A', 'a', '1']))
        ));
    }

    public function testAddsDependenciesOnlyWhenInClassContext(): void
    {
        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($node);

        // we leave the ClassNode, but we haven't entered it, so class context is unknown
        $this->dependencyInspectionVisitor->leaveNode(new ClassNode('test'));
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertEmpty($dependencies);
    }

    public function testTrait(): void
    {
        $node = new TraitNode('Test');
        $node->namespacedName = new Name('A\\Test');
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->addRandomDependency();

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test', new Namespaze(['A']))
        ));
    }

    public function testUseSingleTrait(): void
    {
        $this->addNodeToAst(
            new TraitUse([new Name(['A', 'Test'])])
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test', new Namespaze(['A']))
        ));
    }

    public function testUseMultipleTraits(): void
    {
        $this->addNodeToAst(
            new TraitUse([
                new Name(['A', 'Test']),
                new Name(['B', 'Test2']),
                new Name(['C', 'Test3']),
            ])
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test', new Namespaze(['A']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test2', new Namespaze(['B']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test3', new Namespaze(['C']))
        ));
    }

    public function testUseInstanceofComparison(): void
    {
        $this->addNodeToAst(
            new Instanceof_(new Array_(), new FullyQualifiedNameNode('Test'))
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Test')
        ));
    }

    public function testDetectsCatchNode(): void
    {
        $this->addNodeToAst(
            new Catch_([new Name(['AnException'])], new Variable('e'))
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('AnException')
        ));
    }

    public function testDetectsPhp71MultipleCatchNodes(): void
    {
        $this->addNodeToAst(
            new Catch_([
                new Name(['AnException']),
                new Name(['AnotherException']),
            ], new Variable('e'))
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('AnException')
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('AnotherException')
        ));
    }

    public function testDetectsFetchClassNode(): void
    {
        $this->addNodeToAst(
            new ClassConstFetch(new Name('StaticTest'), 'test')
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('StaticTest')
        ));
    }

    public function testAddsParentDependenciesForExtendableNodesOnly(): void
    {
        $enumNode = new EnumNode('MyEnum');
        $enumNode->namespacedName = new Name('MyNamespace\MyEnum');

        $this->dependencyInspectionVisitor->enterNode($enumNode);
        $this->dependencyInspectionVisitor->leaveNode($enumNode);

        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $enumDependency = new Clazz('MyEnum', new Namespaze(['MyNamespace']));

        $enumDepsSet = $dependencies->get($enumDependency);
        $this->assertTrue($enumDepsSet->isEmpty(), 'Dependency set for Enum should be empty as no parent dependencies should be added.');
    }

    public function testDetectsAttributeDependency(): void
    {
        $attributeGroupNode = new AttributeGroup([
            new Attribute(new Name('MyAttribute')),
            new Attribute(new FullyQualifiedNameNode('Another\\AttributeClass')),
        ]);

        $this->addNodeToAst($attributeGroupNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('MyAttribute')
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('AttributeClass', new Namespaze(['Another']))
        ));
    }

    public function testDetectsUnionTypeParameterDependency(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $unionType = new UnionType([
            new Name('UnionDepOne'),
            new FullyQualifiedNameNode('NS\\UnionDepTwo')
        ]);
        $param = new Param(new Variable('param'), null, $unionType);
        $methodNode->params = [$param];

        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('UnionDepOne')
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('UnionDepTwo', new Namespaze(['NS']))
        ));
    }

    public function testDetectsIntersectionTypeParameterDependency(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $intersectionType = new IntersectionType([
            new Name('IntersectionDepOne'),
            new FullyQualifiedNameNode('NS\\IntersectionDepTwo')
        ]);
        $param = new Param(new Variable('param'), null, $intersectionType);
        $methodNode->params = [$param];

        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('IntersectionDepOne')
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('IntersectionDepTwo', new Namespaze(['NS']))
        ));
    }

    public function testDetectsUnionTypeReturnDependency(): void
    {
        $unionType = new UnionType([
            new Name('ReturnUnionOne'),
            new FullyQualifiedNameNode('NS\\ReturnUnionTwo')
        ]);
        $this->addNodeToAst(
            new ClassMethod('anyMethod', ['returnType' => $unionType])
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ReturnUnionOne')
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ReturnUnionTwo', new Namespaze(['NS']))
        ));
    }

    public function testDetectsIntersectionTypeReturnDependency(): void
    {
        $intersectionType = new IntersectionType([
            new Name('ReturnIntersectionOne'),
            new FullyQualifiedNameNode('NS\\ReturnIntersectionTwo')
        ]);
        $this->addNodeToAst(
            new ClassMethod('anyMethod', ['returnType' => $intersectionType])
        );

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ReturnIntersectionOne')
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ReturnIntersectionTwo', new Namespaze(['NS']))
        ));
    }

    public function testDetectsDynamicClassInstantiationFromStringVariable(): void
    {
        // Setup class and method context
        $classNode = $this->createAndEnterNamedClassNode('TestClass');
        $methodNode = new ClassMethod('testMethod');
        $this->dependencyInspectionVisitor->enterNode($methodNode);

        // Create variable assignment node: $className = 'DynamicClass';
        $stringNode = new String_('dynamicclass');
        $variableNode = new Variable('className');
        $assignNode = new Assign($variableNode, $stringNode);
        $this->dependencyInspectionVisitor->enterNode($assignNode);
        
        // Create instantiation node: $obj = new $className();
        $newNode = new NewNode(new Variable('className'));
        $this->dependencyInspectionVisitor->enterNode($newNode);
        
        // Leave contexts
        $this->dependencyInspectionVisitor->leaveNode($methodNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        
        // Assert that DynamicClass was detected as a dependency
        // Note that our normalization function should capitalize the class name
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Dynamicclass')
        ));
    }

    public function testDetectsDynamicClassInstantiationWithNamespaces(): void
    {
        // Setup class and method context
        $classNode = $this->createAndEnterNamedClassNode('TestClass');
        $methodNode = new ClassMethod('testMethod');
        $this->dependencyInspectionVisitor->enterNode($methodNode);

        // Create variable assignment node: $className = 'Namespace\\DynamicClass';
        $stringNode = new String_('namespace\\dynamicclass');
        $variableNode = new Variable('className');
        $assignNode = new Assign($variableNode, $stringNode);
        $this->dependencyInspectionVisitor->enterNode($assignNode);
        
        // Create instantiation node: $obj = new $className();
        $newNode = new NewNode(new Variable('className'));
        $this->dependencyInspectionVisitor->enterNode($newNode);
        
        // Leave contexts
        $this->dependencyInspectionVisitor->leaveNode($methodNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        
        // Assert that Namespace\DynamicClass was detected as a dependency
        // First letters should be capitalized
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Dynamicclass', new Namespaze(['Namespace']))
        ));
    }

    public function testDetectsDynamicClassInstantiationFromClassConstant(): void
    {
        // Setup class and method context
        $classNode = $this->createAndEnterNamedClassNode('TestClass');
        $methodNode = new ClassMethod('testMethod');
        $this->dependencyInspectionVisitor->enterNode($methodNode);

        // Create variable assignment node: $className = TargetClass::class;
        $classConstFetchNode = new ClassConstFetch(
            new Name('TargetClass'),
            new Identifier('class')
        );
        $variableNode = new Variable('className');
        $assignNode = new Assign($variableNode, $classConstFetchNode);
        $this->dependencyInspectionVisitor->enterNode($assignNode);
        
        // Create instantiation node: $obj = new $className();
        $newNode = new NewNode(new Variable('className'));
        $this->dependencyInspectionVisitor->enterNode($newNode);
        
        // Leave contexts
        $this->dependencyInspectionVisitor->leaveNode($methodNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        
        // Assert that TargetClass was detected as a dependency
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('TargetClass')
        ));
    }

    public function testNormalizesClassNamesCorrectly(): void
    {
        // Setup class and method context
        $classNode = $this->createAndEnterNamedClassNode('TestClass');
        $methodNode = new ClassMethod('testMethod');
        $this->dependencyInspectionVisitor->enterNode($methodNode);

        // Create multiple variable assignments with different casing
        $variables = [
            'var1' => 'lowercase',
            'var2' => 'UPPERCASE',
            'var3' => 'MixedCase',
            'var4' => 'namespace\\lowercased',
            'var5' => 'NAMESPACE\\UPPERCASED',
            'var6' => 'Mixed\\Cased\\Namespace',
        ];
        
        foreach ($variables as $varName => $className) {
            $stringNode = new String_($className);
            $variableNode = new Variable($varName);
            $assignNode = new Assign($variableNode, $stringNode);
            $this->dependencyInspectionVisitor->enterNode($assignNode);
            
            $newNode = new NewNode(new Variable($varName));
            $this->dependencyInspectionVisitor->enterNode($newNode);
        }
        
        // Leave contexts
        $this->dependencyInspectionVisitor->leaveNode($methodNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        
        // Test against the actual dependencies created by the visitor
        // Note: Normalization capitalizes the first letter, but keeps the rest of the casing untouched
        $this->assertTrue($this->hasDependency($dependencies, 'TestClass', 'Lowercase'));
        $this->assertTrue($this->hasDependency($dependencies, 'TestClass', 'UPPERCASE'));
        $this->assertTrue($this->hasDependency($dependencies, 'TestClass', 'MixedCase'));
        $this->assertTrue($this->hasDependency($dependencies, 'TestClass', 'Namespace\\Lowercased'));
        $this->assertTrue($this->hasDependency($dependencies, 'TestClass', 'NAMESPACE\\UPPERCASED'));
        $this->assertTrue($this->hasDependency($dependencies, 'TestClass', 'Mixed\\Cased\\Namespace'));
    }

    public function testIgnoresDynamicInstantiationWithoutTrackedVariables(): void
    {
        // Setup class and method context
        $classNode = $this->createAndEnterNamedClassNode('TestClass');
        $methodNode = new ClassMethod('testMethod');
        $this->dependencyInspectionVisitor->enterNode($methodNode);
        
        // Create instantiation node without prior variable assignment: $obj = new $className();
        $newNode = new NewNode(new Variable('untrackedVariable'));
        $this->dependencyInspectionVisitor->enterNode($newNode);
        
        // Leave contexts
        $this->dependencyInspectionVisitor->leaveNode($methodNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        
        // Verify the TestClass exists in the dependency map but has no outgoing dependencies
        $outgoingDependencies = [];
        $dependencies->each(function (Dependency $from, Dependency $to) use (&$outgoingDependencies) {
            if ($from->toString() === 'TestClass' && $to->toString() !== 'TestClass') {
                $outgoingDependencies[] = $to->toString();
            }
        });
        
        // TestClass should have no outgoing dependencies in this test
        $this->assertEmpty($outgoingDependencies, 'TestClass should have no dependencies on other classes');
    }

    public function testDetectsPhpDocParamDependencies(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @param TestNamespace\\DependencyClass $param
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('DependencyClass', new Namespaze(['TestNamespace']))
        ));
    }

    public function testDetectsPhpDocReturnDependencies(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @return TestNamespace\\ReturnClass
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ReturnClass', new Namespaze(['TestNamespace']))
        ));
    }

    public function testDetectsPhpDocThrowsDependencies(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @throws TestNamespace\\CustomException
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('CustomException', new Namespaze(['TestNamespace']))
        ));
    }

    public function testDetectsPhpDocVarDependencies(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @var TestNamespace\\SomeClass
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('SomeClass', new Namespaze(['TestNamespace']))
        ));
    }

    public function testHandlesPhpDocUnionTypes(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @param Namespace1\\Class1|Namespace2\\Class2 $param
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Class1', new Namespaze(['Namespace1']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Class2', new Namespaze(['Namespace2']))
        ));
    }

    public function testHandlesPhpDocIntersectionTypes(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @param Namespace1\\Class1&Namespace2\\Class2 $param
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Class1', new Namespaze(['Namespace1']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Class2', new Namespaze(['Namespace2']))
        ));
    }

    public function testHandlesPhpDocArrayNotation(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @param ?TestNamespace\\ArrayClass[] $param
         */'));
        
        $this->addNodeToAst($methodNode);

        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ArrayClass', new Namespaze(['TestNamespace']))
        ));
    }

    public function testIgnoresPhpDocPrimitiveTypes(): void
    {
        $methodNode = new ClassMethod('someMethod');
        $methodNode->setDocComment(new Doc('/**
         * @param string $param1
         * @param int $param2
         * @param bool $param3
         * @param array $param4
         * @param mixed $param5
         */'));
        
        $this->addNodeToAst($methodNode);

        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        // The test expects only SomeClass (current class), but we're actually getting
        // an empty dependency list here, which is fine - it means we're successfully
        // not adding any primitive types as dependencies
        $this->assertEmpty($dependencies);
    }

    public function testDetectsPhpDocPropertyAnnotations(): void
    {
        $classNode = new ClassNode('TestClass');
        $classNode->namespacedName = new Name('TestNamespace\\TestClass');
        $classNode->setDocComment(new Doc('/**
         * @property PropertyNamespace\\PropertyClass $property
         * @property-read ReadNamespace\\ReadClass $readOnly
         * @property-write WriteNamespace\\WriteClass $writeOnly
         */'));
        
        $this->dependencyInspectionVisitor->enterNode($classNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        
        $this->assertTrue($this->dependenciesContain(
            $dependencies,
            new Clazz('PropertyClass', new Namespaze(['PropertyNamespace']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $dependencies,
            new Clazz('ReadClass', new Namespaze(['ReadNamespace']))
        ));
        $this->assertTrue($this->dependenciesContain(
            $dependencies,
            new Clazz('WriteClass', new Namespaze(['WriteNamespace']))
        ));
    }

    public function testDetectsPropertyTypeDeclarationDependency(): void
    {
        $classNode = $this->createAndEnterNamedClassNode('A');
        $propertyNode = new \PhpParser\Node\Stmt\Property(
            \PhpParser\Modifiers::PUBLIC,
            [new \PhpParser\Node\Stmt\PropertyProperty('property1')],
            [],
            new Name('B')
        );
        $this->dependencyInspectionVisitor->enterNode($propertyNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('B')
        ));
    }

    public function testDetectsPropertyUnionTypeDeclarationDependency(): void
    {
        $classNode = $this->createAndEnterNamedClassNode('A');
        $unionType = new UnionType([
            new Name('B'),
            new Name('C')
        ]);
        $propertyNode = new \PhpParser\Node\Stmt\Property(
            \PhpParser\Modifiers::PROTECTED,
            [new \PhpParser\Node\Stmt\PropertyProperty('property2')],
            [],
            $unionType
        );
        $this->dependencyInspectionVisitor->enterNode($propertyNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertTrue($this->dependenciesContain($dependencies, new Clazz('B')));
        $this->assertTrue($this->dependenciesContain($dependencies, new Clazz('C')));
    }

    public function testDetectsPropertyIntersectionTypeDeclarationDependency(): void
    {
        $classNode = $this->createAndEnterNamedClassNode('A');
        $intersectionType = new IntersectionType([
            new Name('D'),
            new Name('E')
        ]);
        $propertyNode = new \PhpParser\Node\Stmt\Property(
            \PhpParser\Modifiers::PRIVATE,
            [new \PhpParser\Node\Stmt\PropertyProperty('property3')],
            [],
            $intersectionType
        );
        $this->dependencyInspectionVisitor->enterNode($propertyNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertTrue($this->dependenciesContain($dependencies, new Clazz('D')));
        $this->assertTrue($this->dependenciesContain($dependencies, new Clazz('E')));
    }

    public function testDetectsPropertyNullableTypeDeclarationDependency(): void
    {
        $classNode = $this->createAndEnterNamedClassNode('A');
        $nullableType = new \PhpParser\Node\NullableType(new Name('F'));
        $propertyNode = new \PhpParser\Node\Stmt\Property(
            \PhpParser\Modifiers::PUBLIC,
            [new \PhpParser\Node\Stmt\PropertyProperty('property4')],
            [],
            $nullableType
        );
        $this->dependencyInspectionVisitor->enterNode($propertyNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('F')
        ));
    }

    public function testDetectsTemplateConstraintDependency(): void
    {
        $classNode = new ClassNode('A');
        $classNode->namespacedName = new Name('A');
        $classNode->setDocComment(new Doc("/**
         * @template T of B
         */"));
        $this->dependencyInspectionVisitor->enterNode($classNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertTrue(
            $this->hasDependency($dependencies, 'A', 'B'),
            'Class A should have a dependency on B due to @template T of B.'
        );
    }

    public function testDoesNotDependOnGenericTypeTWithConstraint(): void
    {
        $classNode = new ClassNode('A');
        $classNode->namespacedName = new Name('A');
        $classNode->setDocComment(new Doc("/**
         * @template T of B
         */"));
        $this->dependencyInspectionVisitor->enterNode($classNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertFalse(
            $this->hasDependency($dependencies, 'A', 'T'),
            'Class A should NOT have a dependency on T (the template variable) when using @template T of B.'
        );
    }

    public function testDoesNotDependOnGenericTypeTWithoutConstraint(): void
    {
        $classNode = new ClassNode('A');
        $classNode->namespacedName = new Name('A');
        $classNode->setDocComment(new Doc("/**
         * @template T
         */"));
        $this->dependencyInspectionVisitor->enterNode($classNode);
        $this->dependencyInspectionVisitor->leaveNode($classNode);
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertFalse(
            $this->hasDependency($dependencies, 'A', 'T'),
            'Class A should NOT have a dependency on T (the template variable) when using @template T.'
        );
    }

    private function addNodeToAst(Node $node): void
    {
        $classNode = $this->createAndEnterCurrentClassNode();

        $this->dependencyInspectionVisitor->enterNode($node);
        $this->dependencyInspectionVisitor->leaveNode($node);

        $this->dependencyInspectionVisitor->leaveNode($classNode);
    }
}
