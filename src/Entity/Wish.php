<?php

namespace App\Entity;

use App\Repository\WishRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity("title", message: "This idea already exists!")]
#[ORM\Entity(repositoryClass: WishRepository::class)]
class Wish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Please provide an idea!")]
    #[Assert\Length(
        min: 5, minMessage: "too short!",
        max: 180, maxMessage: "too long! max 180..."
    )]
    #[ORM\Column(length: 180)]
    private ?string $title = null;

    #[Assert\Length(
        min: 5, minMessage: "too short!",
        max: 2000, maxMessage: "too long! max 2000..."
    )]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Assert\NotBlank(message: "Please provide an username!")]
    #[Assert\Regex(
        pattern: "/^[a-z0-9_-]+$/i",
        message: "Your username must match this pattern please: /^[a-z0-9_-]+$/i"
    )]
    #[ORM\Column(length: 50)]
    private ?string $author = null;

    #[ORM\Column(nullable: true, options: ['default' => true])]
    private ?bool $isPublished = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateUpdated = null;


    public function __construct()
    {
        $this->isPublished = true;
        $this->dateCreated = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeImmutable $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeImmutable
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?\DateTimeImmutable $dateUpdated): static
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }
}
