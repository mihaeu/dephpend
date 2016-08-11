<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyPairCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 */
class DependencyPairCollectionTest extends \PHPUnit_Framework_TestCase
{
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
        $expected = (new ClazzCollection())
            ->add(new Clazz('From'))
            ->add(new Clazz('To'))
            ->add(new Clazz('ToAnother'));
        $this->assertEquals($expected, $dependencies->allClasses());
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
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To', new ClazzNamespace(['A', 'a']))))
            ->add(new DependencyPair(new Clazz('FromOther', new ClazzNamespace(['B', 'b'])), new Clazz('SplFileInfo')));
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new ClazzNamespace(['A'])))
            ->add(new DependencyPair(new ClazzNamespace(['B']), new Clazz('SplFileInfo')));
        $actual = $dependencies->filterByDepth(1);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByDepthThree()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(
                new Clazz('From', new ClazzNamespace(['VendorA', 'ProjectA', 'PathA'])),
                new Clazz('To', new ClazzNamespace(['VendorB', 'ProjectB', 'PathB'])))
        );
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(
                    new ClazzNamespace(['VendorA', 'ProjectA', 'PathA']),
                    new ClazzNamespace(['VendorB', 'ProjectB', 'PathB']))
        );
        $actual = $dependencies->filterByDepth(3);
        $this->assertEquals($expected, $actual);
    }
}
