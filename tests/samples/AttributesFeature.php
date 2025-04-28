# A --> B
# A --> C
# A --> D
# A --> E
# A --> F
# A --> G
# A --> H
# A --> I
# A --> J
# A --> K
# A --> L
# A --> M
# A --> N
# A --> O
# A --> P
# A --> Q
# A --> R
# A --> S
# A --> T
# A --> U
<?php

#[B(C::class)]
#[D(E::class)]
class A
{
    #[F(G::class)]
    #[H(I::class)]
    public $defg;

    #[J(K::class)]
    #[L(M::class)]
    protected $hijk;

    #[N(O::class)]
    #[P(Q::class)]
    private $lmnop;

    #[R(S::class)]
    #[T(U::class)]
    public function pqrs(): void
    {
        return;
    }
}
