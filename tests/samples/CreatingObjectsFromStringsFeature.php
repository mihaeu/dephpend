# A --> B
<?php

class A
{
    public function test(): void
    {
        $name = 'b';
        $b = new $name();
    }
}
