<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyPairCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 */
class DependencyPairCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCannotAddDuplicates()
    {
        $this->assertEmpty((new DependencyPairCollection())->add(
            new DependencyPair(new Clazz('X'), new Clazz('X')))
        );
    }

    public function testReturnsTrueIfAnyMatches()
    {
        $to = new Clazz('ToAnother');
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), $to));
        $this->assertTrue($dependencies->any(function (DependencyPair $dependency) use ($to) {
            return $dependency->to() === $to || $dependency->from() === $to;
        }));
    }

    public function testReturnsFalseIfNoneMatches()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('ToAnother')));
        $this->assertFalse($dependencies->any(function (DependencyPair $dependency) {
            return $dependency->to() === new Clazz('Test');
        }));
    }

    public function testEach()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')));
        $dependencies->each(function (DependencyPair $dependency) {
            $this->assertEquals(new DependencyPair(new Clazz('From'), new Clazz('To')), $dependency);
        });
    }

    public function testUniqueRemovesDuplicates()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')));
        $this->assertCount(1, $dependencies->unique());
    }

    public function testReduce()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('ToAnother')));
        $this->assertEquals('ToToAnother', $dependencies->reduce('', function (string $output, DependencyPair $dependency) {
            return $output.$dependency->to()->toString();
        }));
    }

    public function testAllClasses()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('ToAnother')));
        $expected = (new DependencyCollection())
            ->add(new Clazz('From'));
        $this->assertEquals($expected, $dependencies->fromDependencies());
    }

    public function testRemovesInternals()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('SplFileInfo')));
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')));
        $this->assertEquals($expected, $dependencies->removeInternals());
    }

    public function testFilterByDepthOne()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To', new Namespaze(['A', 'a']))))
            ->add(new DependencyPair(new Clazz('FromOther', new Namespaze(['B', 'b'])), new Clazz('SplFileInfo')));
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Namespaze(['A'])))
            ->add(new DependencyPair(new Namespaze(['B']), new Clazz('SplFileInfo')));
        $actual = $dependencies->filterByDepth(1);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByDepthThree()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(
                new Clazz('From', new Namespaze(['VendorA', 'ProjectA', 'PathA'])),
                new Clazz('To', new Namespaze(['VendorB', 'ProjectB', 'PathB'])))
        );
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(
                    new Namespaze(['VendorA', 'ProjectA', 'PathA']),
                    new Namespaze(['VendorB', 'ProjectB', 'PathB']))
        );
        $actual = $dependencies->filterByDepth(3);
        $this->assertEquals($expected, $actual);
    }
}
