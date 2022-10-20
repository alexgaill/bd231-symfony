<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message:"Le champs {{ label }} ne peut pas être vide")]
    #[Assert\Length(
        min:5,
        minMessage:"Le titre de l'article doit faire au minimum {{ limit }} caractères",
        max:100,
        maxMessage:"Le titre de l'article ne peut excéder {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:"Le champs {{ label }} ne peut pas être vide")]
    #[Assert\Length(
        min:10,
        minMessage:"Le contenu de l'article doit faire au minimum {{ limit }} caractères",
        max:1000,
        maxMessage:"Le contenu de l'article ne peut excéder {{ limit }} caractères."
    )]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, updatable:false)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    // #[Assert\Choice(callback: [Category::class, 'getCategory'])]
    private ?Category $category = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $picture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    #[ORM\PostRemove]
    public function deletePicture(): void
    {
        if (file_exists(__DIR__ . "/../../public/assets/img/posts/upload/". $this->picture)) {
            unlink(__DIR__ . "/../../public/assets/img/posts/upload/". $this->picture);
        }
    }
}
