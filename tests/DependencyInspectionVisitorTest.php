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
 * @uses Mihaeu\PhpDependencies\ClazzDependencies
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

        $this->assertArrayHasKey('SomeNamespace.SomeClass', $this->dependencyInspectionVisitor->dependencies());
    }

    public function testDetectsExplicitNewCreation()
    {
        $node = new NewNode(new FullyQualifiedNameNode('TestDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertEquals(new Clazz('TestDep'), $this->dependencyInspectionVisitor->dependencies()['GLOBAL']->dependencies()[0]);
    }

    public function testDetectsImplicitNewCreation()
    {
        $node = new NewNode(new VariableNode('TestDep'));
        $this->dependencyInspectionVisitor->leaveNode($node);
        $this->assertEquals(new Clazz('TestDep'), $this->dependencyInspectionVisitor->dependencies()['GLOBAL']->dependencies()[0]);
    }
}
