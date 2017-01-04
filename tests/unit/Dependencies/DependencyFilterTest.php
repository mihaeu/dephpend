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

    public function testMapUnderscoreNamespaces()
    {
        $this->assertEquals(
            DependencyHelper::map('
                A\\b\\c --> D\\e\\f
                F\\a --> D\\b
                _A\\b --> _B\\c
            '),
            $this->filter->mapNamespaces(DependencyHelper::map('
                A_b_c --> D_e_f
                F_a --> D_b
                _A_b --> _B_c
            '))
        );
    }

    public function testMapUnderscoreNamespacesAlreadyNamespace()
    {
        $this->assertEquals(
            DependencyHelper::map('
                VendorA\\Tests\\DDC1209_1 --> a\\To
                A\\__b__\\c --> D\\e\\f
            '),
            $this->filter->mapNamespaces(DependencyHelper::map('
                VendorA\\Tests\\DDC1209_1 --> a\\To
                A\\__b__\\c --> D\\e\\f
            '))
        );
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

    public function testThrowsExceptionForBadRegex()
    {
        $options = [
            'internals'             => false,
            'filter-from'           => 'A',
            'depth'                 => 2,
            'filter-namespace'      => 'A',
            'no-classes'            => true,
            'exclude-regex'         => 'Missing brackets',
            'underscore-namespaces' => true,
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->filter->filterByOptions(new DependencyMap(), $options);
    }

    public function testRunAllFilters()
    {
        $options = [
            'internals'             => false,
            'filter-from'           => 'A',
            'depth'                 => 2,
            'filter-namespace'      => 'A',
            'no-classes'            => true,
            'exclude-regex'         => '/Test/',
            'underscore-namespaces' => true,
        ];
        $dependencies = DependencyHelper::map('
            A\\a\\z --> B\\b\\z
            A\\a\\z --> A\\b\\z
            A\\a\\Test --> A\\b\\z
            A_b_c --> A_b_z
        ');
        $actual = $this->filter->filterByOptions($dependencies, $options);
        $expected = DependencyHelper::map('
            A\\a\\z --> A\\b\\z
            A\\b\\c --> A\\b\\z
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
