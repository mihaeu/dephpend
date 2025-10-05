# MyTrait --> OtherTrait1
# MyTrait --> OtherTrait2
<?php

trait MyTrait
{
    use OtherTrait1;
    use OtherTrait2;
    
    public function traitMethod(): void
    {
        // Implementation
    }
}
