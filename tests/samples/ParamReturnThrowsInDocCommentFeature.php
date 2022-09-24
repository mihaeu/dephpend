<?php

declare(strict_types=1);

$test = '
class A {
    /**
     * @param B $b
     * @param $c C
     *
     * @return D
     *
     * @throws E
     */
    public function a() {
        return 1;
    }
}';

preg_match_all('/
    @(param|return|throws)
    \h+
    ((\?<!\$)[a-zA-Z0-9_\\]+)
    \h+
    $[a-zA-Z0-9_]+
    \s*\v/mx', $test, $matches);
var_dump($matches);
