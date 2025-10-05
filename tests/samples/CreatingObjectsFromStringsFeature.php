# A --> B
# A --> C
# A --> Namespace\ClassName
<?php

class A
{
    public function test(): void
    {
        // Basic string variable instantiation case-insensitive
        $name = 'b';
        $b = new $name();
        
        // Class constant instantiation
        $name2 = C::class;
        $obj2 = new $name2();
        
        // Using namespaced class name
        $name3 = 'Namespace\\ClassName';
        $obj3 = new $name3();
    }
}
