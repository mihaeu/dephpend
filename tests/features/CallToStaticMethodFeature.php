# A --> Singleton
<?php

class A
{
    public function test()
    {
        Singleton::create();
    }
}

class Singleton
{
    private static $instance;

    private function __construct()
    {
        // ...
    }

    public static function create()
    {
        if (self::$instance === null) {
            self::$instance = new Singleton();
        }
        return self::$instance;
    }
}
