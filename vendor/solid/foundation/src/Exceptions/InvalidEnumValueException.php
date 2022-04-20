<?php

namespace Solid\Foundation\Exceptions;

use Exception;
use Solid\Foundation\Enum;

class InvalidEnumValueException extends Exception
{
    /** @var \Solid\Foundation\Enum */
    protected $enum;
    /** @var string */
    protected $value;

    /**
     * InvalidEnumValueException constructor.
     *
     * @param \Solid\Foundation\Enum $enum
     * @param string                 $value
     */
    public function __construct(Enum $enum, string $value)
    {
        parent::__construct(
            sprintf('%s is not a valid value %s',
                $value,
                implode(', ', $enum->toArray())
            ),
            402
        );

        $this->enum = $enum;
        $this->value = $value;
    }


}