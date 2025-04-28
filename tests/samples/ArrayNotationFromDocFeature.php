# A --> B
# A --> C
# A --> D
# A --> E
# A --> F
# A --> G
# A --> H
<?php

/**
 * @template T 
 */
class A
{
    /**
     * @param B[] $b
     * @param ?C[] $c
     * @param D|null[] $d
     * @param E|F[] $e
     * @param G&H[] $f
     * @param T[] $t
     */
    public function test($b, $c, $d, $e, $f, $g, $h, $t)
    {
        return 'test';
    }
}
