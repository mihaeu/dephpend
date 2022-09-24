# B --> A
<?php

enum A: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}

class B
{
    public function x(): A
    {
        return A::Foo;
    }
}
