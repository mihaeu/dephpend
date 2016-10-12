<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Util\Functional;

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
        $this->assertEquals($expected, $this->filter->removeInternals($dependencies));
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
        $actual = $this->filter->filterByDepth($dependencies, 1);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByDepthThree()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\ProjectA\\PathA\\From --> VendorB\\ProjectB\\PathB\\To
        ');
        $expected = DependencyHelper::map('_VendorA\\ProjectA\\PathA --> _VendorB\\ProjectB\\PathB');
        $actual = $this->filter->filterByDepth($dependencies, 3);
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
        $this->assertEquals($expected, $this->filter->filterByNamespace($dependencies, 'VendorA'));
    }

    public function testFilterByDepth0ReturnsEqual()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\A --> VendorB\\A
            VendorA\\A --> VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $this->assertEquals($dependencies, $this->filter->filterByDepth($dependencies, 0));
    }
    public function testRemoveClasses()
    {
        $expected = DependencyHelper::map('
            _VendorA --> _VendorB
            _VendorB --> _VendorA
        ');
        $actual = $this->filter->filterClasses(DependencyHelper::map('
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
        '), $this->filter->filterByFromNamespace(DependencyHelper::map('
            Good\\A --> Bad\\B
            Good\\B --> Good\\C
            Bad\\B --> Good\\A
        '), 'Good'));
    }

    public function testRunAllFilters()
    {
        $options = [
            'internals'         => false,
            'filter-from'       => 'A',
            'depth'             => 2,
            'filter-namespace'  => 'A',
            'no-classes'        => true,
            'exclude-regex'     => '/Test/',
        ];
        $dependencies = DependencyHelper::map('
            A\\a\\z --> B\\b\\z
            A\\a\\z --> A\\b\\z
            A\\a\\Test --> A\\b\\z
        ');
        $actual = $this->filter->filterByOptions($dependencies, $options);
        $expected = DependencyHelper::map('
            A\\a\\z --> A\\b\\z
        ');
        $this->assertEquals($expected, $actual);
    }

    public function testExcludeByRegex()
    {
        $this->assertEquals(DependencyHelper::map('
            X --> Z
        '), $this->filter->excludeByRegex(DependencyHelper::map('
            Test\\A --> B
            B --> Test\\C
            B --> C\\Test
            D --> Example
            Example --> Test\\Test
            X --> Z
        '), '/(Test)|(Example)/'));
    }

    public function testPostFilters()
    {
        $filters = $this->filter->postFiltersByOptions(['no-classes' => true, 'depth' => 1]);
        $this->assertEquals(new Namespaze(['A']), $filters(new Clazz('Test', new Namespaze(['A', 'a']))));
    }
}
