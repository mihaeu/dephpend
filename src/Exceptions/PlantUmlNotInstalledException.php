<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Exceptions;

class PlantUmlNotInstalledException extends \Exception
{
    public function __construct()
    {
        parent::__construct('PlantUML installation not found.');
    }
}
