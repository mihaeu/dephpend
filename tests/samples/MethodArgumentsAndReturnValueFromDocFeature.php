# A --> B
# A --> C
# A --> D
<?php

class A
{
    /**
     * @param B $b
     *
     * @return C|D
     */
    public function test($b)
    {
        return $b->call();
    }
}
