<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;
use PhpParser\Node\Stmt\Use_ as UseNode;

/**
 * @covers Mihaeu\PhpDependencies\DependencyInspectionVisitor
 */
class DependencyInspectionVisitorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor;

    public function setUp()
    {
        $this->dependencyInspectionVisitor = new DependencyInspectionVisitor();
        $this->dependencyInspectionVisitor->beforeTraverse([]);
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
            new Clazz('ClassA', new ClazzNamespace(['A', 'a', '1']))
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
            new Clazz('InterfaceOne', new ClazzNamespace(['A', 'B'])))
        );
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('InterfaceTwo', new ClazzNamespace(['C', 'D'])))
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
            new Clazz('DependencyOne', new ClazzNamespace(['A', 'B'])))
        );
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('DependencyTwo', new ClazzNamespace(['A', 'B'])))
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
            new Clazz('Test', new ClazzNamespace(['A', 'a', '1']))
        ));
    }

    public function testDetectsCallsOnStaticClasses()
    {
        $node = $this->createAndEnterCurrentClassNode();

        $staticCall = new MethodCall(
            new StaticCall(new Name('Singleton'), 'Singleton'),
            'staticMethod'
        );
        $staticCall->var->class->parts = ['A', 'a', '1', 'Singleton'];
        $this->dependencyInspectionVisitor->enterNode($staticCall);

        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertTrue($this->dependenciesContain(
            $this->dependencyInspectionVisitor->dependencies(),
            new Clazz('Singleton', new ClazzNamespace(['A', 'a', '1']))
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

    /**
     * @param DependencyPairCollection $dependencies
     * @param Dependency               $otherDependency
     *
     * @return bool
     */
    private function dependenciesContain(DependencyPairCollection $dependencies, Dependency $otherDependency) : bool
    {
        return $dependencies->any(function (DependencyPair $dependency) use ($otherDependency) {
            return $dependency->from()->equals($otherDependency)
            || $dependency->to()->equals($otherDependency);
        });
    }

    /**
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
}
