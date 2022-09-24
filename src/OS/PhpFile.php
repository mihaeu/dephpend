<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Exceptions\FileDoesNotExistException;
use Mihaeu\PhpDependencies\Exceptions\FileIsNotReadableException;

class PhpFile
{
    /** @var \SplFileObject */
    private $file;

    public function __construct(\SplFileInfo $file)
    {
        $this->ensureFileExists($file);
        $this->ensureFileIsReadable($file);
        $this->file = $file;
    }

    public function file(): \SplFileInfo
    {
        return $this->file;
    }

    public function equals(PhpFile $other)
    {
        return $this->file()->getPathname() === $other->file()->getPathname();
    }

    public function code()
    {
        return @file_get_contents($this->file->getPathname());
    }

    public function toString(): string
    {
        return (string) $this->file;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    private function ensureFileExists(\SplFileInfo $file)
    {
        if (!$file->isFile() && !$file->isDir()) {
            throw new FileDoesNotExistException($file);
        }
    }

    private function ensureFileIsReadable(\SplFileInfo $file)
    {
        if (!$file->isReadable()) {
            throw new FileIsNotReadableException($file);
        }
    }
}
