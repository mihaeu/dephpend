<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class DependencyStructureMatrixHtmlFormatter extends DependencyStructureMatrixFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyCollection $dependencyCollection) : string
    {
        $dependencyArray = $this->buildMatrix(
            $dependencyCollection,
            $dependencyCollection->allClasses()
        );

        return $this->buildHtmlTable($dependencyArray);
    }

    /**
     * @param array $dependencyArray
     *
     * @return string
     */
    private function buildHtmlTable(array $dependencyArray) : string
    {
        $output = [
            '<table><tr><th>X</th><th>'.implode('</th><th>',
                array_keys($dependencyArray)),
        ];
        $output[] = '</th></tr>';

        foreach ($dependencyArray as $dependencyRow => $dependencies) {
            $output[] = '<tr><td>'.$dependencyRow.'</td>';
            foreach ($dependencies as $dependencyHeader => $count) {
                if ($dependencyRow === $dependencyHeader) {
                    $output[] = '<td>X</td>';
                } else {
                    $output[] = '<td>'.$count.'</td>';
                }
            }
            $output[] = '</tr>';
        }
        $output[] = '</table>';

        return implode('', $output);
    }
}
