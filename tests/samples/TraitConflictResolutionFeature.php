# MyClass --> Trait1
# MyClass --> Trait2
<?php

class MyClass
{
    use Trait1, Trait2 {
        Trait1::commonMethod insteadof Trait2;
        Trait2::anotherMethod insteadof Trait1;
    }
}
