# A --> B
# B --> C
<?php

class A
{
    public function test(): void
    {
        $x = (new B())->x();
    }
}

class B
{
    public function x() : C
    {
        return new C();
    }
}
