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
            $dependencyCollection->allClasses($dependencyCollection)
        );

        return $this->buildHtmlTable($dependencyArray);
    }

    /**
     * @param DependencyCollection $dependencyCollection
     * @param ClazzCollection      $clazzCollection
     *
     * @return array
     */
    private function buildMatrix(DependencyCollection $dependencyCollection, ClazzCollection $clazzCollection) : array
    {
        $emptyDsm = $clazzCollection->reduce([], function (array $combined, Clazz $clazz) use ($clazzCollection) {
            $combined[$clazz->toString()] = array_combine(array_values($clazzCollection->toArray()), array_pad([], $clazzCollection->count(), 0));

            return $combined;
        });

        return $dependencyCollection->reduce($emptyDsm, function (array $dsm, Dependency $dependency) use ($emptyDsm) {
            $dsm[$dependency->from()->toString()][$dependency->to()->toString()] = 1;

            return $dsm;
        });
    }

    /**
     * @param array $dependencyArray
     *
     * @return string
     */
    private function buildHtmlTable(array $dependencyArray) : string
    {
        $output = [
            '<table><tr><th></th><th>'.implode('</th><th>',
                array_keys($dependencyArray)),
        ];
        $output[] = '</th></tr>';

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
}
