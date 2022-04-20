<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Solid\Foundation\Traits\Stringable;

class _TestStringableCaseClass {
    use Stringable;

    public function toString(): string
    {
        return 'ok';
    }
}

class StringableTest extends TestCase
{
    /** @var _TestStringableCaseClass */
    protected $case;

    /**
     * StringableTest constructor.
     */
    public function __construct()
    {
        parent::__construct('Stringable test case');
        $this->case = new _TestStringableCaseClass;
    }


    public function testBasicUsage(): void
    {
        static::assertNotEmpty($this->case->toString());
        static::assertEquals('ok', $this->case->toString());
    }

    public function testCastUsage(): void
    {
        static::assertNotEmpty((string) $this->case);
        static::assertEquals('ok', (string) $this->case);
    }
}