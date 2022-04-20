<?php

namespace Solid\Foundation\Traits;

trait Arrayable
{
    /**
     * Array representation of a value
     * @return array
     */
    abstract public function toArray(): array;
}