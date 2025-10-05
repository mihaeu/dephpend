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
# A --> V
# A --> W
# A --> X
# A --> Y
<?php

/**
 * @property B&C $property
 * @property-read D&E $property2
 * @property-write F&G $property3
 */
class A
{
    public H&I $property;

    protected J&K $property2;

    private L&M $property3;

    /**
     * @var N&O
     */
    public $property4;

    /**
     * @var P&Q
     */
    protected $property5;

    /**
     * @var R&S
     */
    private $property6;

    public function test(T&U&V $param2): W&X
    {
        return new Y();
    }
}
