<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

interface Parser
{
    public function parse(string $code): array;
}
