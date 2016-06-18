<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\Interface_ as InterfaceNode;

/**
 * @covers Mihaeu\PhpDependencies\DependencyInspectionVisitor
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClazzCollection
 * @uses Mihaeu\PhpDependencies\Dependency
 * @uses Mihaeu\PhpDependencies\DependencyCollection
 * @uses Mihaeu\PhpDependencies\AbstractCollection
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

    public function testDetectsClassName()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->afterTraverse([]);
        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->findClassesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('TestDep'), $classesDependingOnSomeClass->toArray()[0]);
    }

    public function testDetectsExplicitNewCreation()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->afterTraverse([]);
        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->findClassesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('TestDep'), $classesDependingOnSomeClass->toArray()[0]);
    }

    public function testDetectsImplicitNewCreation()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $node = new NewNode(new VariableNode('$testDep'));
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->afterTraverse([]);
        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->findClassesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('$testDep'), $classesDependingOnSomeClass->toArray()[0]);
    }

    public function testDetectsExtendedClasses()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $node->extends = new \stdClass();
        $node->extends->parts = ['SomeSuperClass'];
        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->afterTraverse([]);
        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->findClassesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('SomeSuperClass'), $classesDependingOnSomeClass->toArray()[0]);
    }

    public function testDetectsImplementedInterfaces()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $interfaceOneNode = new InterfaceNode('InterfaceOne');
        $interfaceOneNode->parts = ['Namespace', 'InterfaceOne'];
        $interfaceTwoNode = new InterfaceNode('InterfaceTwo');
        $interfaceTwoNode->parts = ['Namespace', 'InterfaceTwo'];
        $node->implements = [$interfaceOneNode, $interfaceTwoNode];

        $this->dependencyInspectionVisitor->enterNode($node);

        $this->dependencyInspectionVisitor->afterTraverse([]);
        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->findClassesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('Namespace.InterfaceOne'), $classesDependingOnSomeClass->toArray()[0]);
        $this->assertEquals(new Clazz('Namespace.InterfaceTwo'), $classesDependingOnSomeClass->toArray()[1]);
    }

    public function testDetectsDependenciesFromMethodArguments()
    {
        self::fail();
    }
}
