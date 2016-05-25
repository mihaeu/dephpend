<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

class Finder
{
    public function find(\SplFileInfo $file) : PhpFileCollection
    {
        return $file->isDir()
            ? $this->findInDir($file)
            : new PhpFileCollection();
    }

    private function findInDir(\SplFileInfo $dir) : PhpFileCollection
    {
        return new PhpFileCollection();
    }
}
