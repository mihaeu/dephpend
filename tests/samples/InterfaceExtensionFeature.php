# ChildInterface --> ParentInterface1
# ChildInterface --> ParentInterface2
<?php

interface ChildInterface extends ParentInterface1, ParentInterface2
{
    public function additionalMethod(): void;
}
