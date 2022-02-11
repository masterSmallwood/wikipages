<?php

use PHPUnit\Framework\TestCase;
use App\Validators\ValidateCommandInput;

class CommandInputValidationTest extends TestCase
{
    public function testCanValidateDates(): void
    {
        $this->assertTrue(ValidateCommandInput::date('2020-01-01'));
        $this->assertFalse(ValidateCommandInput::date('2020-01-'));
        $this->assertFalse(ValidateCommandInput::date('2020'));
        $this->assertFalse(ValidateCommandInput::date('2020-01-01-01'));
    }

    public function testCanValidateHours(): void
    {
        $this->assertTrue(ValidateCommandInput::hour('0'));
        $this->assertTrue(ValidateCommandInput::hour('23'));
        $this->assertTrue(ValidateCommandInput::hour('12'));
        $this->assertFalse(ValidateCommandInput::hour('-1'));
        $this->assertFalse(ValidateCommandInput::hour('hello'));
        $this->assertFalse(ValidateCommandInput::hour('24'));
    }
}