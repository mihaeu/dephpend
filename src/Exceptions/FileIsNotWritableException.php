<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Exceptions;

class FileIsNotWritableException extends \Exception
{
    public function __construct(\SplFileInfo $file)
    {
        parent::__construct($file->getPathname() . ' is not writable.');
    }
}
