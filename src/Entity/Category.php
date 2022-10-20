<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Category{

    #[ORM\Column(type:'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    private int $id;

    #[ORM\Column(type:'string', length:60, unique:true)]
    #[Assert\NotBlank(message:"Le champs {{ label }} ne peut pas être vide")]
    #[Assert\Length(
        min:5,
        minMessage:"Le nom de la catégorie doit faire au minimum {{ limit }} caractères",
        max:60,
        maxMessage:"La catégorie ne peut excéder {{ limit }} caractères."
    )]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Post::class, orphanRemoval: true)]
    private Collection $posts;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $picture = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setCategory($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }

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
        if (file_exists(__DIR__."/../../public/assets/img/categories/upload/" . $this->picture)) {
            unlink(__DIR__."/../../public/assets/img/categories/upload/" . $this->picture);
        }
    }
}