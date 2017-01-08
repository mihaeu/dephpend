<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

class Tarjan
{
    private $cycles = [];
    private $marked = [];
    private $markedStack = [];
    private $pointStack = [];

    private $adjacencyList;

    public function stronglyConnectedComponents(array $adjacencyList) : array
    {
        $this->adjacencyList = $adjacencyList;
        $this->marked = array_fill(0, count($adjacencyList), false);

        foreach ($adjacencyList as $i => $neighbors) {
            $this->recursiveTarjan($i, $i);
            while (!empty($this->markedStack)) {
                $this->marked[array_pop($this->markedStack)] = false;
            }
        }

        return array_values($this->cycles);
    }

    public function stronglyConnectedComponentsFromDependencyMap(DependencyMap $dependencyMap) : array
    {
        $indexToDependencies = $dependencyMap->allDependencies()->mapToArray(function (Dependency $dependency) {
            return $dependency->toString();
        });
        $dependenciesToIndex = array_combine(
            array_values($indexToDependencies),
            array_keys($indexToDependencies)
        );
        $initial = array_combine(array_keys($indexToDependencies), array_fill(0, count($indexToDependencies), []));
        $adjacencyListFromDependencies = $dependencyMap->reduce($initial, function (array $adjacencyList, Dependency $from, Dependency $to) use ($dependenciesToIndex) {
            $adjacencyList[$dependenciesToIndex[$from->toString()]][] = $dependenciesToIndex[$to->toString()];
            return $adjacencyList;
        });
        $stronglyConnectedComponents = $this->stronglyConnectedComponents($adjacencyListFromDependencies);
        return array_map(function (array $vertices) use ($indexToDependencies) {
            return array_map(function (int $vertex) use ($indexToDependencies) {
                return $indexToDependencies[$vertex];
            }, $vertices);
        }, $stronglyConnectedComponents);
    }

    /*
     * Recursive function to detect strongly connected components (cycles, loops).
     */
    private function recursiveTarjan(int $s, int $v) : bool
    {
        $f = false;
        $this->pointStack[] = $v;
        $this->marked[$v] = true;
        $this->markedStack[] = $v;

        //$maxlooplength = 3; // Enable to Limit the length of loops to keep in the results (see below).

        foreach ($this->adjacencyList[$v] as $w) {
            if ($w < $s) {
                $this->adjacencyList[$w] = [];
            } elseif ($w == $s) {
                //if (count($point_stack) == $maxlooplength){ // Enable to collect cycles of a given length only.
                // Add new cycles as array keys to avoid duplication. Way faster than using array_search.
                $this->cycles[implode('|', $this->pointStack)] = $this->pointStack;
                //}
                $f = true;
            } elseif ($this->marked[$w] === false) {
                //if (count($point_stack) < $maxlooplength){ // Enable to only collect cycles up to $maxlooplength.
                $g = $this->recursiveTarjan($s, $w);
                //}
                if (!empty($g)) {
                    $f = true;
                }
            }
        }

        if ($f === true) {
            while (end($this->markedStack) != $v) {
                $this->marked[array_pop($this->markedStack)] = false;
            }
            array_pop($this->markedStack);
            $this->marked[$v] = false;
        }

        array_pop($this->pointStack);
        return $f;
    }
}
