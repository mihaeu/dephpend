<?php

namespace Mihaeu\PhpDependencies\Exceptions;

class ParserException extends \Exception
{
    public function __construct(string $message, string $file)
    {
        $this->message = $message;
        $this->file = $file;
    }
}
