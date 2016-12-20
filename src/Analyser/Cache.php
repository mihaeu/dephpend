<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;
use PhpParser\Node;

interface Cache
{
    public function has(PhpFile $file) : bool;

    public function get(PhpFile $file) : array;

    public function set(PhpFile $file, Node ...$nodes);
}
