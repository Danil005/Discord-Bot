<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Solid\Foundation\Traits\Arrayable;

final class _TestArrayableCaseClass {
    use Arrayable;

    public function toArray(): array
    {
        return [1, 2, 3];
    }
}

class ArrayableTest extends TestCase
{
    /** @var _TestArrayableCaseClass */
    protected $case;

    /**
     * ArrayableTest constructor.
     */
    public function __construct()
    {
        parent::__construct('Arrayable test case');
        $this->case = new _TestArrayableCaseClass();
    }

    public function testBasicUsage(): void
    {
        static::assertIsArray($this->case->toArray());
        static::assertNotEmpty($this->case->toArray());
    }
}