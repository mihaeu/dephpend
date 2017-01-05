<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

interface Dependency extends \Countable
{
    public function reduceToDepth(int $maxDepth) : Dependency;

    public function reduceDepthFromLeftBy(int $reduction) : Dependency;

    public function equals(Dependency $other) : bool;

    public function toString() : string;

    public function namespaze() : Namespaze;

    public function isNamespaced() : bool;

    public function inNamespaze(Namespaze $other) : bool;
}
