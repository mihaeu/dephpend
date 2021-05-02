<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Exceptions;

class DotNotInstalledException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Dot (Graphviz) installation not found.');
    }
}
