<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyPairSet;

interface Formatter
{
    /**
     * @param DependencyPairSet $dependencyCollection
     *
     * @return string
     */
    public function format(DependencyPairSet $dependencyCollection) : string;
}
