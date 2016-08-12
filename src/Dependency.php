<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

interface Dependency
{
    public function depth() : int;

    public function reduceToDepth(int $maxDepth) : Dependency;

    public function equals(Dependency $other) : bool;
}
