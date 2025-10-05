# A --> B
# A --> C
<?php

class A
{
    public function test(): void
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
