<?php

namespace App\Entity;

use App\Repository\PostRepository;
use App\Traits\TimeStampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Post
{
    use TimeStampTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $media = null;

    #[ORM\Column]
    private ?int $reactCount = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?User $User = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'Post', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var Collection<int, React>
     */
    #[ORM\OneToMany(targetEntity: React::class, mappedBy: 'Post', orphanRemoval: true)]
    private Collection $reacts;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reacts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getReactCount(): ?int
    {
        return $this->reactCount;
    }

    public function setReactCount(int $reactCount): static
    {
        $this->reactCount = $reactCount;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, React>
     */
    public function getReacts(): Collection
    {
        return $this->reacts;
    }

    public function addReact(React $react): static
    {
        if (!$this->reacts->contains($react)) {
            $this->reacts->add($react);
            $react->setPost($this);
        }

        return $this;
    }

    public function removeReact(React $react): static
    {
        if ($this->reacts->removeElement($react)) {
            // set the owning side to null (unless already changed)
            if ($react->getPost() === $this) {
                $react->setPost(null);
            }
        }
        return $this;
    }

}
