<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\OS\PhpFile;

interface Parser
{
    public function parse(PhpFile $file) : array;
}
