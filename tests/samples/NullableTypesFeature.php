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
<?php

/**
 * @property ?B $property
 * @property-read ?C $property2
 * @property-write ?D $property3
 */
class A
{
    public ?E $property;

    protected ?F $property2;

    private ?G $property3;

    /**
     * @var ?H
     */
    public $property4;

    /**
     * @var ?I
     */
    protected $property5;

    /**
     * @var ?J
     */
    private $property6;

    public function test(?K $param1): ?L
    {
        return null;
    }
}
