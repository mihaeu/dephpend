<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

interface Formatter
{
    /**
     * @param DependencyPairCollection $dependencyCollection
     *
     * @return string
     */
    public function format(DependencyPairCollection $dependencyCollection) : string;
}
