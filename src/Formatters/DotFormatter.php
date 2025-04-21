<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Util\Functional;

class DotFormatter implements Formatter
{
    public function format(DependencyMap $map, ?\Closure $mappers = null): string
    {
        return 'digraph generated_by_dePHPend {'.PHP_EOL
            .$map->reduceEachDependency($mappers ?? Functional::id())->reduce('', function (string $carry, Dependency $from, Dependency $to) {
                return $carry."\t\"".str_replace('\\', '.', $from->toString().'" -> "'.$to->toString().'"').PHP_EOL;
            })
            .'}';
    }
}
