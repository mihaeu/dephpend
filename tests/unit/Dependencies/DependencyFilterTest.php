<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencyFilter
 */
class DependencyFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyFilter */
    private $filter;

    public function setUp()
    {
        $this->filter = new DependencyFilter(['SplFileInfo']);
    }

    public function testRemovesInternals()
    {
        $dependencies = DependencyHelper::map('From --> To, SplFileInfo');
        $expected = (new DependencyMap())->add(new Clazz('From'), new Clazz('To'));
        $this->assertEquals($expected, $this->filter->removeInternals()($dependencies));
    }

    public function testFilterByDepthOne()
    {
        $dependencies = DependencyHelper::map('
            From --> A\\a\\To
            B\\b\\FromOther --> SplFileInfo
        ');
        $expected = DependencyHelper::map('
            From --> _A
            _B --> SplFileInfo
        ');
        $actual = $this->filter->filterByDepth(1)($dependencies);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByDepthThree()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\ProjectA\\PathA\\From --> VendorB\\ProjectB\\PathB\\To
        ');
        $expected = DependencyHelper::map('_VendorA\\ProjectA\\PathA --> _VendorB\\ProjectB\\PathB');
        $actual = $this->filter->filterByDepth(3)($dependencies);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByVendor()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $expected = DependencyHelper::map('
            VendorA\\A --> VendorA\\C
        ');
        $this->assertEquals($expected, $this->filter->filterByNamespace('VendorA')($dependencies));
    }

    public function testFilterByDepth0ReturnsEqual()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\A --> VendorB\\A
            VendorA\\A --> VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $this->assertEquals($dependencies, $this->filter->filterByDepth(0)($dependencies));
    }
    public function testRemoveClasses()
    {
        $expected = DependencyHelper::map('
            _VendorA --> _VendorB
            _VendorB --> _VendorA
        ');
        $actual = $this->filter->filterClasses()(DependencyHelper::map('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> B
        '));
        $this->assertEquals($expected, $actual);
    }

    public function testFilterFromDependencies()
    {
        $this->assertEquals(DependencyHelper::map('
            Good\\A --> Bad\\B
            Good\\B --> Good\\C
        '), $this->filter->filterByFromNamespace('Good')(DependencyHelper::map('
            Good\\A --> Bad\\B
            Good\\B --> Good\\C
            Bad\\B --> Good\\A
        ')));
    }

    public function testRunAllFilters()
    {
        $options = [
            'internals'         => false,
            'filter-from'       => 'A',
            'depth'             => 2,
            'filter-namespace'  => 'A',
            'no-classes'        => true,
        ];
        $dependencies = DependencyHelper::map('
            A\\a\\z --> B\\b\\z
            A\\a\\z --> A\\b\\z
        ');
        $actual = $this->filter->filterByOptions($dependencies, $options);
        $expected = DependencyHelper::map('
            _A\\a --> _A\\b
        ');
        $this->assertEquals($expected, $actual);
    }
}
