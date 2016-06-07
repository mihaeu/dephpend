<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node\Expr\New_ as NewNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedNameNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;

/**
 * @covers Mihaeu\PhpDependencies\DependencyInspectionVisitor
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClazzCollection
 * @uses Mihaeu\PhpDependencies\Dependency
 * @uses Mihaeu\PhpDependencies\ClazzDependencies
 * @uses Mihaeu\PhpDependencies\AbstractCollection
 */
class DependencyInspectionVisitorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyInspectionVisitor */
    private $dependencyInspectionVisitor;

    public function setUp()
    {
        $this->dependencyInspectionVisitor = new DependencyInspectionVisitor();
    }

    public function testDetectsClassName()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->leaveNode($node);

        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);

        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->classesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('TestDep'), $classesDependingOnSomeClass->toArray()[0]);
    }

    public function testDetectsExplicitNewCreation()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->leaveNode($node);

        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);

        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->classesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('TestDep'), $classesDependingOnSomeClass->toArray()[0]);
    }

    public function testDetectsImplicitNewCreation()
    {
        $node = new ClassNode('SomeClass');
        $node->namespacedName = new \stdClass();
        $node->namespacedName->parts = ['SomeNamespace', 'SomeClass'];
        $this->dependencyInspectionVisitor->leaveNode($node);

        $node = new NewNode(new VariableNode('$testDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);

        $classesDependingOnSomeClass = $this->dependencyInspectionVisitor
            ->dependencies()
            ->classesDependingOn(new Clazz('SomeNamespace.SomeClass'));
        $this->assertEquals(new Clazz('$testDep'), $classesDependingOnSomeClass->toArray()[0]);
    }
}
