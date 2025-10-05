# A --> B
# A --> C
<?php

class A
{
    public function test(): void
    {
        try {
            throw new B("Invalid operation");
        } catch (C $e) {
            return;
        }
    }
}
