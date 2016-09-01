<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection;

interface Formatter
{
    /**
     * @param DependencyPairCollection $dependencyCollection
     *
     * @return string
     */
    public function format(DependencyPairCollection $dependencyCollection) : string;
}
