<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\CustomTrait\TimestampableTrait;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
  use TimestampableTrait;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 180, unique: true)]
  private ?string $email = null;

  #[ORM\ManyToOne(inversedBy: 'users')]
  private ?Client $Client = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): static
  {
    $this->id = $id;

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(string $email): static
  {
    $this->email = $email;

    return $this;
  }

  public function getUserIdentifier(): string
  {
    return (string) $this->email;
  }


  public function getClient(): ?Client
  {
    return $this->Client;
  }

  public function setClient(?Client $Client): static
  {
    $this->Client = $Client;

    return $this;
  }

  public function getUsername(): ?string
  {
    return $this->getUserIdentifier();
  }
}
