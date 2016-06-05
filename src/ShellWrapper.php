<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class ShellWrapper
{
    private $STD_ERR_PIPE = ' 2> /dev/null';

    /**
     * @param string $command
     *
     * @return int return var
     */
    public function run(string $command) : int
    {
        $output = [];
        $returnVar = 1;
        exec($command.$this->STD_ERR_PIPE, $output, $returnVar);

        return $returnVar;
    }
}
