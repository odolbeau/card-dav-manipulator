<?php

namespace Olidol\DAV\Manipulator\Exception;

class DifferentValuesForUniqPropertyException extends \Exception
{
    protected $property;
    protected $values;

    public function __construct($property, array $values)
    {
        $this->property = $property;
        $this->values = $values;

        parent::__construct(sprintf(
            'Found different values for property "%s". Values: "%s"',
            $property,
            implode('", "', $values)
        ));
    }
}
