<?php
// src/Domain/Model/User.php
namespace App\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\ValueObject\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true)]
    private string $uid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    private function __construct(
        UserId $id,
        string $name,
        string $password,
        string $email,
        \DateTimeImmutable $createdAt
    ) {
        $this->uid = $id->getValue();
        $this->name = $name;
        // TODO: encode pwd
        $this->password = $password;
        $this->email = $email;
        $this->createdAt = $createdAt;
    }

    public static function create(
        string $name,
        string $email,
        string $plainPassword
    ): self {
        $name = trim($name);
        $email = strtolower(trim($email));

        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }
        
        $id = UserId::create();
        $encodedPassword = self::hashPassword($plainPassword);
        $createdAt = new \DateTimeImmutable();
        
        return new self($id, $name, $encodedPassword, $email, $createdAt);
    }

    private static function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

    public function id(): UserId 
    {
        return UserId::fromString($this->uid);
    }

    public function name(): string 
    {
        return $this->name;
    }

    public function email(): string 
    {
        return $this->email;
    }
    
    public function password(): string 
    {
        return $this->password;
    }
}