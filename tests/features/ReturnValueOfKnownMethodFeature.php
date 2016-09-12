# A --> B
# A --> C
# B --> C
<?php

class A
{
    public function test()
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
