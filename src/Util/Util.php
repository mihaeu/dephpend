<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

class Util
{
    /**
     * @param array    $xs
     * @param \Closure $fn
     *                     Tests every element of xs against this function. The first
     *                     parameter is the value of the current element, the second
     *                     is the index of the current element
     *
     * @return bool
     */
    public static function array_once(array $xs, \Closure $fn) : bool
    {
        foreach ($xs as $index => $value) {
            if ($fn($value, $index)) {
                return true;
            }
        }

        return false;
    }

    /**
     * PHP's array_reduce does not provide access to the key. This function
     * does the same as array produce, while providing access to the key.
     *
     * @param array $xs
     * @param \Closure $fn (mixed $carry, int|string $key, mixed $value)
     * @param $initial
     *
     * @return mixed
     */
    public static function reduce(array $xs, \Closure $fn, $initial)
    {
        foreach ($xs as $key => $value) {
            $initial = $fn($initial, $key, $value);
        }
        return $initial;
    }
}
