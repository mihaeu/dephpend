<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;

interface Formatter
{
    /**
     * @param DependencyMap $map
     *
     * @return string
     */
    public function format(DependencyMap $map, ?\Closure $mappers = null) : string;
}
