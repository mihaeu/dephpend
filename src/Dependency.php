<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

interface Dependency
{
    public function depth() : int;
}
