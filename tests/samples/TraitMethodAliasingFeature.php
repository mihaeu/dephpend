# MyClass --> SomeTrait
<?php

class MyClass
{
    use SomeTrait {
        traitMethod as aliasedMethod;
        privateMethod as public;
        otherMethod as protected newName;
    }
}
