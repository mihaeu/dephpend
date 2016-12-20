<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Exceptions\CacheDirectoryCannotBeCreatedException;
use Mihaeu\PhpDependencies\OS\PhpFile;
use PhpParser\Node;

class FileCache implements Cache
{
    /** @var \SplFileInfo */
    private $root;

    /** @var array */
    private $keyLookup = [];

    public function __construct(\SplFileInfo $root = null)
    {
        if ($root === null) {
            $root = new \SplFileInfo(sys_get_temp_dir().'/.dephpend.cache');
            if (@mkdir($root->getPathname()) && !is_dir($root->getPathname())) {
                throw new CacheDirectoryCannotBeCreatedException($root);
            }
        }
        $this->root = $root;
    }

    private function generateKey(PhpFile $file)
    {
        if (!array_key_exists($file->file()->getPathname(), $this->keyLookup)) {
            $this->keyLookup[$file->file()->getPathname()] = $file->file()->getBasename().$file->file()->getMTime();
        }
        return $this->keyLookup[$file->file()->getPathname()];
    }

    public function has(PhpFile $file) : bool
    {
        return file_exists($this->root.'/'.$this->generateKey($file));
    }

    public function get(PhpFile $file) : array
    {
        return unserialize(
            file_get_contents($this->root.'/'.$this->generateKey($file))
        );
    }

    public function set(PhpFile $file, Node ...$nodes)
    {
        file_put_contents(
            $this->root.'/'.$this->generateKey($file),
            serialize($nodes)
        );
    }
}
