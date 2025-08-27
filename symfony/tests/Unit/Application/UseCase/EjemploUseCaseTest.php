<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\Application\UseCase\EjemploUseCase;

final class EjemploUseCaseTest extends TestCase
{
    public function test_execute(): void
    {
        $service = new EjemploUseCase();

        $greeting = $service->execute('Alice');

        $this->assertSame('Alice', $greeting);
    }

    public function test_execute_with_empty_param__should_throw_invalid_argument_exception() : void 
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new EjemploUseCase();

        $greeting = $service->execute('');
    }
}