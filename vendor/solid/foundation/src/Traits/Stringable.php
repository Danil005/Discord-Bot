<?php

namespace Solid\Foundation\Traits;

trait Stringable {
    /**
     * String representation of a value
     * @return string
     */
    abstract public function toString(): string;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}