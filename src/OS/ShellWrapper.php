<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

class ShellWrapper
{
    private $STD_ERR_PIPE = ' 2>&1 /dev/null';

    private $STD_ERR_PIPE_WIN = ' 2> NUL';

    /**
     * @param string $command
     *
     * @return int return var
     */
    public function run(string $command) : int
    {
        $output = [];
        $returnVar = 1;

        $command .= $this->isWindows() ? $this->STD_ERR_PIPE_WIN : $this->STD_ERR_PIPE;

        exec($command, $output, $returnVar);

        return $returnVar;
    }

    private function isWindows(): bool
    {
        return 0 === stripos(PHP_OS, 'WIN');
    }
}
