<?php 
namespace App\Tests\Integration\Application\UseCase;

use App\Application\UseCase\User\RegisterUseCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Model\User;
use App\Tests\ResetDatabaseTrait;
use Doctrine\ORM\EntityManagerInterface;

final class RegisterUseCaseTest extends KernelTestCase
{
    private ?RegisterUseCase $registerUseCase;
    private EntityManagerInterface $entityManager;
    
    use ResetDatabaseTrait;
    
    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        
        $this->resetDatabase();

        // Obtener el servicio mediante el container
        $container = static::getContainer();
        $this->registerUseCase = $container->get(RegisterUseCase::class);
    }

    public function test_registering_user_with_correct_data__should_return_expected_object(): void
    {
        $result = $this->registerUseCase->register('mi nombre', 'mail@mail.com', 'plainpassword');

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_registering_user_with_correct_data__should_return_expected_object_with_expected_data(): void
    {
        $result = $this->registerUseCase->register('mi nombre', 'mail@mail.com', 'plainpassword');

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('mi nombre', $result->name());
        $this->assertSame('mail@mail.com', $result->email());
        $this->assertTrue($result->verifyPassword('plainpassword'));
    }

    public function test_registering_user_with_correct_data__should_persist_it(): void
    {
        $result = $this->registerUseCase->register('mi nombre', 'mail@mail.com', 'plainpassword');

        $this->assertInstanceOf(User::class, $result);

        $users = $this->entityManager->getRepository(User::class)->findAll();
        $this->assertCount(1, $users);

        $user = $users[0];
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('mi nombre', $user->name());
        $this->assertSame('mail@mail.com', $user->email());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->registerUseCase = null;
    }
}