<?php

namespace App\Tests\Unit\Infrastructure\Http\Commands;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Http\Commands\RegisterUserCommand;

class RegisterUserCommandTest extends TestCase
{
    public function test_valid_command_should_work(): void
    {
        $command = new RegisterUserCommand('John', 'john@test.com', 'password123');
        
        $this->assertEquals('John', $command->name());
        $this->assertEquals('john@test.com', $command->email());
    }

    public function test_invalid_email_should_throw_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        new RegisterUserCommand('John', 'invalid-email', 'password123');
    }
}