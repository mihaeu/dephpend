<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Util\Functional;

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
    public function format(DependencyMap $all, \Closure $mappers = null) : string
    {
        return $this->buildHtmlTable(
            $this->dependencyStructureMatrixBuilder->buildMatrix($all, $mappers ?? Functional::id())
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
            ."</table><script>
['forEach', 'map', 'filter', 'reduce', 'reduceRight', 'every', 'some'].forEach(
    function(p) {
    NodeList.prototype[p] = HTMLCollection.prototype[p] = Array.prototype[p];
});
document.getElementsByTagName('td').forEach(x => (x.innerText === '0' || x.innerText === 'X') && (x.style.color = 'lightgray'));
            const max = document.getElementsByTagName('td').map(x => x.innerText === 'X' ? 0 : parseInt(x.innerText, 10)).reduce((prev, cur) => prev > cur ? prev : cur);
            document.getElementsByTagName('td').forEach(x => x.innerText !== '0' && x.innerText !== 'X' && (x.className += ' weight-'+ Math.ceil(x.innerText / (max/10))));
            </script>
            <style>
table {
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid #333; 
}

td, th {
    vertical-align: middle; 
    height: 20px;
}

td { 
    width: 20px;
    text-align: center;
}

.weight-1 {
	background-color: #ffffff;
}

.weight-2 {
	background-color: #ffffe6;
}

.weight-3 {
	background-color: #ffffcc;
}

.weight-4 {
	background-color: #ffffb3;
}

.weight-5 {
	background-color: #ffff99;
}

.weight-6 {
	background-color: #ffff80;
}

.weight-7 {
	background-color: #ffff66;
}

.weight-8 {
	background-color: #ffff4d;
}

.weight-9 {
	background-color: #ffff33;
}

.weight-10 {
	background-color: #ffff1a;
}
            </style>";
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
