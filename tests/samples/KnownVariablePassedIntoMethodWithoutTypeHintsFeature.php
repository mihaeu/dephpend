# A --> B
# A --> C
# C --> B
<?php

class A
{
    public function test()
    {
        $b = new B;
        new C($b);
    }
}

class C
{
    public function __construct($b)
    {
        $b->call();
    }
}
