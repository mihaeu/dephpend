<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Tarjan
 */
class TarjanTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleCycle()
    {
        $this->assertEquals([['B', 'X1', 'X2']], (new Tarjan())->stronglyConnectedComponentsFromDependencyMap(DependencyHelper::map('
            A --> B
            B --> C
            B --> X1
            X1 --> X2
            X2 --> B
        ')));
    }

    public function testTarjan()
    {
        $adjacencyList = [
            0  => [1],
            1  => [4, 6, 7],
            2  => [4, 6, 7],
            3  => [4, 6, 7],
            4  => [2, 3],
            5  => [2, 3],
            6  => [5, 8],
            7  => [5, 8],
            8  => [],
            9  => [],
            10 => [10],       // This is a self-cycle (aka "loop")
        ];

        // there are 11 results for the above example (strictly speaking: 10 cycles and 1 loop):
        $this->assertEquals([
            [2, 4],
            [2, 4, 3, 6, 5],
            [2, 4, 3, 7, 5],
            [2, 6, 5],
            [2, 6, 5, 3, 4],
            [2, 7, 5],
            [2, 7, 5, 3, 4],
            [3, 4],
            [3, 6, 5],
            [3, 7, 5],
            [10],
        ], (new Tarjan())->stronglyConnectedComponents($adjacencyList));
    }

    public function testTarjanWithDependencies()
    {
        $this->assertEquals([
            ['Class4', 'Class2'],
            ['Class4', 'Class2', 'Class6', 'Class5', 'Class3'],
            ['Class4', 'Class2', 'Class7', 'Class5', 'Class3'],
            ['Class4', 'Class3'],
            ['Class4', 'Class3', 'Class6', 'Class5', 'Class2'],
            ['Class4', 'Class3', 'Class7', 'Class5', 'Class2'],
            ['Class6', 'Class5', 'Class2'],
            ['Class6', 'Class5', 'Class3'],
            ['Class7', 'Class5', 'Class2'],
            ['Class7', 'Class5', 'Class3']
        ], (new Tarjan())->stronglyConnectedComponentsFromDependencyMap(DependencyHelper::map('
            Class0  --> Class1
            Class1  --> Class4, Class6, Class7
            Class2  --> Class4, Class6, Class7
            Class3  --> Class4, Class6, Class7
            Class4  --> Class2, Class3
            Class5  --> Class2, Class3
            Class6  --> Class5, Class8
            Class7  --> Class5, Class8
        ')));
    }
}
