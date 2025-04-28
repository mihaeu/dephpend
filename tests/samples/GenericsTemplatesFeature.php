# A --> B
# A --> C
<?php

/**
 * @template T of B
 */
class A
{
    /** @var class-string<T> */
    private string $tClass;
    
    /**
     * @param class-string<T> $tClass
     */
    public function __construct(string $tClass)
    {
        $this->tClass = $tClass;
    }
    
    /**
     * @return C<int, T>
     */
    public function c()
    {
        return new C();
    }
}
