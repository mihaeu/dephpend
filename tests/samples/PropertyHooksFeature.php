# A --> B
# A --> C
# A --> D
# C --> B
# D --> B
<?php

class A
{
    private B $b {
        get {
            if ($this->b === null) {
                $c = new C();
                $this->b = $c->b();
            }
            return $this->b;
        }
        
        set(?B $val) {
            if ($val === null) {
                $d = new D();
                $this->b = $d->b();
            } else {
                $this->b = $val;
            }
        }
    }
}

class B
{
}

class C
{
    public function b()
    {
        return new B();
    }
}

class D
{
    public function b()
    {
        return new B();
    }
}
