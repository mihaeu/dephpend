<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Exceptions;

class FileIsNotReadableException extends \Exception
{
    public function __construct(\SplFileInfo $file)
    {
        parent::__construct($file->getPathname() . ' is not readable.');
    }
}
