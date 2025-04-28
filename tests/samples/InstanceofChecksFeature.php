# A --> B
# A --> C
<?php

class A
{
    public function test($object): void
    {
        if ($object instanceof B) {
            return;
        } elseif ($object instanceof C) {
            return;
        }
    }
}
