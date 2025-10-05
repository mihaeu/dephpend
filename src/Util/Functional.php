<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util   ;

use Closure;

class Functional
{
    /**
     * @return Closure (mixed $x): mixed
     */
    public static function id()
    {
        return function ($x) {
            return $x;
        };
    }

    /**
     * @param Closure $functions
     */
    public static function compose(...$functions): Closure
    {
        return array_reduce(
            $functions,
            function (Closure $carry, Closure $item): Closure {
                return function ($x) use ($carry, $item) {
                    return $item($carry($x));
                };
            },
            Functional::id()
        );
    }
}
