<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyStructureMatrixHtmlFormatter implements Formatter
{
    /** @var DependencyStructureMatrixBuilder */
    private $dependencyStructureMatrixBuilder;

    /**
     * @param DependencyStructureMatrixBuilder $dependencyStructureMatrixBuilder
     */
    public function __construct(DependencyStructureMatrixBuilder $dependencyStructureMatrixBuilder)
    {
        $this->dependencyStructureMatrixBuilder = $dependencyStructureMatrixBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function format(DependencyPairCollection $dependencyCollection) : string
    {
        $dependencyArray = $this->dependencyStructureMatrixBuilder->buildMatrix($dependencyCollection);

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
            '<table><thead><tr><th>X</th><th>'.implode('</th><th>',
                array_keys($dependencyArray)),
        ];
        $output[] = '</th></tr></thead><tbody>';

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
        $output[] = '</tbody></table>';

        return implode('', $output);
    }
}
