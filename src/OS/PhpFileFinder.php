<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

class PhpFileFinder
{
    public function find(\SplFileInfo $file) : PhpFileSet
    {
        return $file->isDir()
            ? $this->findInDir($file)
            : (new PhpFileSet())->add(new PhpFile($file));
    }

    private function findInDir(\SplFileInfo $dir) : PhpFileSet
    {
        $collection = new PhpFileSet();
        $regexIterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir->getPathname())
            ), '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH
        );
        foreach ($regexIterator as $fileName) {
            $collection = $collection->add(new PhpFile(new \SplFileInfo($fileName[0])));
        }

        return $collection;
    }
}
