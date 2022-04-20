<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Solid\Foundation\Enum;
use Solid\Foundation\Exceptions\InvalidEnumValueException;

class _TestCaseEnum extends Enum {
    const OPTION1 = '1';
    const OPTION2 = '2';
    const OPTION3 = '3';
}

class EnumTest extends TestCase
{
    public function testValidEnumValue(): void
    {
        $enum = _TestCaseEnum::of(_TestCaseEnum::OPTION2);
        static::assertEquals(_TestCaseEnum::OPTION2, $enum);
    }

    public function testValidEnumValueOther(): void
    {
        $enum = _TestCaseEnum::of(_TestCaseEnum::OPTION1);
        static::assertFalse($enum->is(_TestCaseEnum::OPTION3));
    }

    public function testInValidEnumValue(): void
    {
        $this->expectException(InvalidEnumValueException::class);
        _TestCaseEnum::of('invalid');
    }
}