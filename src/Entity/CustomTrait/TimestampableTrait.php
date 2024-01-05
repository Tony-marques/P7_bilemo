<?php

namespace App\Entity\CustomTrait;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
  #[ORM\Column()]
  private ?DateTimeImmutable $createdAt = null;

  #[ORM\Column()]
  private ?DateTimeImmutable $updatedAt = null;

  public function getCreatedAt(): DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function setCreatedAt(DateTimeImmutable $createdAt): self
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  public function getUpdatedAt(): DateTimeImmutable
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(DateTimeImmutable $updatedAt): self
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }
}
