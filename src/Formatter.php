<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

interface Formatter
{
    /**
     * @param DependencyCollection $dependencyCollection
     *
     * @return string
     */
    public function format(DependencyCollection $dependencyCollection) : string;
}
