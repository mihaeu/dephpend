# A --> B
# A --> C
<?php

class A
{
    public function test(): void
    {
        new B();
        new C;
    }
}
