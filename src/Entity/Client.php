<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\CustomTrait\TimestampableTrait;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{
  use TimestampableTrait;

  #[ORM\Column()]
  #[ORM\GeneratedValue()]
  #[ORM\Id]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $name = null;

  #[ORM\Column(length: 255)]
  private ?string $email = null;

  #[ORM\Column(length: 255)]
  private ?string $password = null;

  #[ORM\Column()]
  private array $roles = [];

  #[ORM\OneToMany(mappedBy: "Client", targetEntity: User::class, orphanRemoval: true)]
  #[Groups(["user:rdddead"])]
  private Collection $users;

  public function __construct()
  {
    $this->users = new ArrayCollection();
  }

  public function getUserIdentifier(): string
  {
    return (string) $this->email;
  }

  public function eraseCredentials(): void
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }
  /**
   * Get the value of id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set the value of id
   *
   * @return  self
   */
  public function setId(int $id): self
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Get the value of name
   */
  public function getName(): ?string
  {
    return $this->name;
  }

  /**
   * Set the value of name
   *
   * @return  self
   */
  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get the value of email
   */
  public function getEmail(): string
  {
    return $this->email;
  }

  /**
   * Set the value of email
   *
   * @return  self
   */
  public function setEmail(string $email): self
  {
    $this->email = $email;

    return $this;
  }

  /**
   * Get the value of password
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  /**
   * Set the value of password
   *
   * @return  self
   */
  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Get the value of roles
   */
  public function getRoles(): array
  {
    return $this->roles;
  }

  /**
   * Set the value of roles
   *
   * @return  self
   */
  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  public function getUsername(): ?string
  {
    return $this->getUserIdentifier();
  }

  /**
   * @return Collection<int, User>
   */
  public function getUsers(): Collection
  {
    return $this->users;
  }

  public function addUser(User $user): static
  {
    if (!$this->users->contains($user)) {
      $this->users->add($user);
      $user->setClient($this);
    }

    return $this;
  }

  public function removeUser(User $user): static
  {
    if ($this->users->removeElement($user)) {
      // set the owning side to null (unless already changed)
      if ($user->getClient() === $this) {
        $user->setClient(null);
      }
    }

    return $this;
  }
}
