<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

class PhpFile
{
    /** @var \SplFileObject */
    private $file;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }
}
