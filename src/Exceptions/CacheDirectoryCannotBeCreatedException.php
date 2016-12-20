<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Exceptions;

class CacheDirectoryCannotBeCreatedException extends \Exception
{
    public function __construct(\SplFileInfo $file)
    {
        parent::__construct($file->getPathname() . ' cache directory could not be created.');
    }
}
