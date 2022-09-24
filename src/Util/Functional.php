<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util   ;

class Functional
{
    public static function id()
    {
        return function ($x) {
            return $x;
        };
    }

    public static function compose(...$functions): \Closure
    {
        return array_reduce(
            $functions,
            function ($carry, $item) {
                return function ($x) use ($carry, $item) {
                    return $item($carry($x));
                };
            },
            Functional::id()
        );
    }
}
