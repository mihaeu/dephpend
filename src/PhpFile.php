<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class PhpFile
{
    /** @var \SplFileObject */
    private $file;

    public function __construct(\SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function file() : \SplFileInfo
    {
        return $this->file;
    }

    public function equals(PhpFile $other)
    {
        return $this->file()->getPathname() === $other->file()->getPathname();
    }

    public function code()
    {
        return file_get_contents($this->file->getPathname());
    }
}
