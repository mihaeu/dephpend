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
        return $this->buildHtmlTable(
            $this->dependencyStructureMatrixBuilder->buildMatrix($dependencyCollection)
        );
    }

    /**
     * @param array $dependencyArray
     *
     * @return string
     */
    private function buildHtmlTable(array $dependencyArray) : string
    {
        return '<table>'
            .$this->tableHead($dependencyArray)
            .$this->tableBody($dependencyArray)
            .'</table>';
    }

    /**
     * @param array $dependencyArray
     *
     * @return string
     */
    private function tableBody(array $dependencyArray)
    {
        $output = '<tbody>';
        $numIndex = 1;
        foreach ($dependencyArray as $dependencyRow => $dependencies) {
            $output .= "<tr><th>$numIndex: $dependencyRow</th>";
            foreach ($dependencies as $dependencyHeader => $count) {
                if ($dependencyRow === $dependencyHeader) {
                    $output .= '<td>X</td>';
                } else {
                    $output .= '<td>' . $count . '</td>';
                }
            }
            $output .= '</tr>';
            $numIndex += 1;
        }
        $output .= '</tbody>';
        return $output;
    }

    /**
     * @param array $dependencyArray
     *
     * @return string
     */
    private function tableHead(array $dependencyArray)
    {
        $output = '<thead><tr><th>X</th>';
        for ($i = 1, $len = count($dependencyArray); $i <= $len; $i += 1) {
            $output .= "<th>$i</th>";
        }
        return $output . '</tr></thead>';
    }
}
