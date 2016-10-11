<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\AbstractClazz;
use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\Interfaze;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;
use Mihaeu\PhpDependencies\Dependencies\Trait_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_ as UseNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;

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
 * @covers Mihaeu\PhpDependencies\Analyser\DependencyInspectionVisitor
 */
class DependencyInspectionVisitorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor;

    public function setUp()
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
    private function createAndEnterCurrentClassNode()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->enterNode($node);

        return $node;
    }

    /**
     * Helper method which adds a random dependency when testing if the from-
     * dependency is picked up and the to-dependency is not important.
     */
    private function addRandomDependency()
    {
        $newNode = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($newNode);
        $this->dependencyInspectionVisitor->leaveNode($newNode);
    }

    public function testDetectsExplicitNewCreation()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $newNode = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($newNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain($this->dependencyInspectionVisitor->dependencies(), new Clazz('TestDep')));
    }

    public function testDetectsExtendedClasses()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];

        $node->extends = new \stdClass();
        $node->extends->parts = ['A', 'a', '1', 'ClassA'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('ClassA', new Namespaze(['A', 'a', '1']))
        ));
    }

    public function testDetectsWhenInterfacesImplementMultipleInterfaces()
    {
        $node = new InterfaceNode('SomeInterface');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeInterface'];

        $node->extends = [
            new \stdClass(),
            new \stdClass()
        ];
        $node->extends[0]->parts = ['A', 'a', '1', 'ClassA'];
        $node->extends[1]->parts = ['B', 'b', '2', 'ClassB'];
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

    public function testDetectsAbstractClasses()
    {
        $node = new ClassNode('Test', ['type' => 16]);
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['A', 'Test'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->addRandomDependency();

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new AbstractClazz('Test', new Namespaze(['A']))
        ));
    }

    public function testDetectsInterfaces()
    {
        $node = new Interface_('Test');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['A', 'Test'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->addRandomDependency();

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Interfaze('Test', new Namespaze(['A']))
        ));
    }

    public function testDetectsImplementedInterfaces()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];

        $interfaceOneNode = new InterfaceNode('InterfaceOne');
        $interfaceOneNode->parts = ['A', 'B', 'InterfaceOne'];
        $interfaceTwoNode = new InterfaceNode('InterfaceTwo');
        $interfaceTwoNode->parts = ['C', 'D', 'InterfaceTwo'];
        $node->implements = [$interfaceOneNode, $interfaceTwoNode];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('InterfaceOne', new Namespaze(['A', 'B'])))
        );
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('InterfaceTwo', new Namespaze(['C', 'D'])))
        );
    }

    public function testDetectsDependenciesFromMethodArguments()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $methodNode = new ClassMethod('someMethod');
        $paramOne = new Param('one', null, 'DependencyOne');
        $paramOne->type = new \stdClass();
        $paramOne->type->parts = ['A', 'B', 'DependencyOne'];
        $paramTwo = new Param('two', null, 'DependencyTwo');
        $paramTwo->type = new \stdClass();
        $paramTwo->type->parts = ['A', 'B', 'DependencyTwo'];
        $methodNode->params = [
            $paramOne,
            $paramTwo,
        ];
        $this->dependencyInspectionVisitor->enterNode($methodNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('DependencyOne', new Namespaze(['A', 'B'])))
        );
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('DependencyTwo', new Namespaze(['A', 'B'])))
        );
    }

    public function testDetectsUseNodes()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $use = new \stdClass();
        $use->name = new \stdClass();
        $use->name->parts = ['A', 'a', '1', 'Test'];
        $useNode = new UseNode([$use]);
        $this->dependencyInspectionVisitor->enterNode($useNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Test', new Namespaze(['A', 'a', '1']))
        ));
    }

    public function testReturnType()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $methodNode = new ClassMethod('');
        $methodNode->returnType = new FullyQualifiedNameNode(['Namespace', 'Test']);
        $this->dependencyInspectionVisitor->enterNode($methodNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Test', new Namespaze(['Namespace']))
        ));
    }

    public function testDetectsCallsOnStaticClasses()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $staticCall = new StaticCall(new Name('Singleton'), 'Singleton');
        $staticCall->class->parts = ['A', 'a', '1', 'Singleton'];
        $this->dependencyInspectionVisitor->enterNode($staticCall);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Singleton', new Namespaze(['A', 'a', '1']))
        ));
    }

    public function testAddsDependenciesOnlyWhenInClassContext()
    {
        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($node);

        // we leave the ClassNode, but we haven't entered it, so class context is unknown
        $this->dependencyInspectionVisitor->leaveNode(new ClassNode('test'));
        $dependencies = $this->dependencyInspectionVisitor->dependencies();
        $this->assertEmpty($dependencies);
    }

    public function testTrait()
    {
        $node = new TraitNode('Test');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['A', 'Test'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->addRandomDependency();

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test', new Namespaze(['A']))
        ));
    }

    public function testUseSingleTrait()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $useTraitNode = new TraitUse(['Trait']);
        $useTraitNode->traits = [new \stdClass()];
        $useTraitNode->traits[0]->parts = ['A', 'Test'];

        $this->dependencyInspectionVisitor->enterNode($useTraitNode);
        $this->dependencyInspectionVisitor->leaveNode($useTraitNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Trait_('Test', new Namespaze(['A']))
        ));
    }

    public function testUseMultipleTraits()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $useTraitNode = new TraitUse(['Trait']);
        $useTraitNode->traits = [new \stdClass(), new \stdClass(), new \stdClass()];
        $useTraitNode->traits[0]->parts = ['A', 'Test'];
        $useTraitNode->traits[1]->parts = ['B', 'Test2'];
        $useTraitNode->traits[2]->parts = ['C', 'Test3'];

        $this->dependencyInspectionVisitor->enterNode($useTraitNode);
        $this->dependencyInspectionVisitor->leaveNode($useTraitNode);

        $this->dependencyInspectionVisitor->leaveNode($node);
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
}
