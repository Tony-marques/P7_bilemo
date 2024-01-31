<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\CustomTrait\TimestampableTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use OpenApi\Attributes as OA;

// #[OA\Schema]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["product:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["product:read"])]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    #[Groups(["product:read"])]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[Groups(["product:read"])]
    private ?string $description = null;

    #[ORM\Column()]
    #[Groups(["product:read"])]
    private ?int $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
