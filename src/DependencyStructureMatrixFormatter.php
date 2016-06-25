<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class DependencyStructureMatrixFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyCollection $dependencyCollection) : string
    {
        $dependencyArray = $this->buildMatrix(
            $dependencyCollection,
            $this->allClasses($dependencyCollection)
        );
        $output = ['<table><tr><th></th><th>'.implode('</th><th>', array_keys($dependencyArray))];
        $output[] = '</th></tr>';
        // build table body
        foreach ($dependencyArray as $dependencyRow => $dependencies) {
            $output[] = '<tr><td>'.$dependencyRow.'</td>';
            foreach ($dependencies as $dependencyHeader => $count) {
                $output[] = '<td>'.$count.'</td>';
            }
            $output[] = '</tr>';
        }
        $output[] = '</table>';

        return implode('', $output);
    }

    private function buildMatrix(DependencyCollection $dependencyCollection, array $allClasses) : array
    {
        $allClassesAsKeys = array_reduce($allClasses, function (array $allClassesAsKeys, string $clazzName) {
            $allClassesAsKeys[$clazzName] = 0;

            return $allClassesAsKeys;
        }, []);
        $emptyDsm = array_reduce($allClasses, function (array $dsm, string $clazzName) use ($allClassesAsKeys) {
            $dsm[$clazzName] = $allClassesAsKeys;

            return $dsm;
        }, []);

        return $dependencyCollection->reduce($emptyDsm, function (array $dsm, Dependency $dependency) use ($emptyDsm) {
            $dsm[$dependency->from()->toString()][$dependency->to()->toString()] = 1;

            return $dsm;
        });
    }

    private function allClasses(DependencyCollection $dependencyCollection) : array
    {
        return $dependencyCollection->reduce([], function (array $dependencyCount, Dependency $dependency) {
            if (!in_array($dependency->from()->toString(), $dependencyCount, true)) {
                $dependencyCount[] = $dependency->from()->toString();
            }
            if (!in_array($dependency->to()->toString(), $dependencyCount, true)) {
                $dependencyCount[] = $dependency->to()->toString();
            }

            return $dependencyCount;

//            if (!array_key_exists($dependency->from()->toString(), $dependencyCount)) {
//                $dependencyCount[$dependency->from()->toString()] = [
//                    $dependency->to()->toString() => 0
//                ];
//            }
//            $dependencyCount[$dependency->from()->toString()][$dependency->to()->toString()] += 1;
//            return $dependencyCount;
        });
    }
}
