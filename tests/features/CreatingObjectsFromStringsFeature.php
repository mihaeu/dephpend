# A --> B
<?php

class A
{
    public function test()
    {
        $name = 'b';
        $b = new $name();
    }
}
