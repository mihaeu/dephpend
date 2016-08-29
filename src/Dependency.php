<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

interface Dependency extends \Countable
{
    public function reduceToDepth(int $maxDepth) : Dependency;

    public function reduceDepthFromLeftBy(int $reduction) : Dependency;

    public function equals(Dependency $other) : bool;

    public function toString() : string;
}
