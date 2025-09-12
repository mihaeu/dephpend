<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\DependencyHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Dependencies\DependencyFilter::class)]
class DependencyFilterTest extends TestCase
{
    /** @var DependencyFilter */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new DependencyFilter(['SplFileInfo']);
    }

    public function testRemovesInternals(): void
    {
        $dependencies = DependencyHelper::map('From --> To, SplFileInfo');
        $expected = (new DependencyMap())->add(new Clazz('From'), new Clazz('To'));
        $this->assertEquals($expected, $this->filter->removeInternals($dependencies));
    }

    public function testFilterByDepthOne(): void
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

    public function testMapUnderscoreNamespaces(): void
    {
        $this->assertEquals(
            DependencyHelper::map('
                A\\b\\c --> D\\e\\f
                F\\a --> D\\b
            '),
            $this->filter->mapNamespaces(DependencyHelper::map('
                A_b_c --> D_e_f
                F_a --> D_b
            '))
        );
    }

    public function testMapUnderscoreNamespacesAlreadyNamespace(): void
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

    public function testFilterByDepthThree(): void
    {
        $dependencies = DependencyHelper::map('
            VendorA\\ProjectA\\PathA\\From --> VendorB\\ProjectB\\PathB\\To
        ');
        $expected = DependencyHelper::map('_VendorA\\ProjectA\\PathA --> _VendorB\\ProjectB\\PathB');
        $actual = $this->filter->filterByDepth($dependencies, 3);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByVendor(): void
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

    public function testFilterByDepth0ReturnsEqual(): void
    {
        $dependencies = DependencyHelper::map('
            VendorA\\A --> VendorB\\A
            VendorA\\A --> VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $this->assertEquals($dependencies, $this->filter->filterByDepth($dependencies, 0));
    }
    public function testRemoveClasses(): void
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

    public function testFilterFromDependencies(): void
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

    public function testThrowsExceptionForBadRegex(): void
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
        $this->expectException(InvalidArgumentException::class);
        $this->filter->filterByOptions(new DependencyMap(), $options);
    }

    public function testRunAllFilters(): void
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

    public function testExcludeByRegex(): void
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

    public function testPostFilters(): void
    {
        $filters = $this->filter->postFiltersByOptions(['no-classes' => true, 'depth' => 1]);
        $this->assertEquals(new Namespaze(['A']), $filters(new Clazz('Test', new Namespaze(['A', 'a']))));
    }
}
