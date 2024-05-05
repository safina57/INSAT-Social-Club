<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $fullname = null;

    #[ORM\Column(length: 50)]
    private ?string $username = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 24, options: ["default" => "Offline"])]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;
//
//    /**
//     * @var Collection<int, Post>
//     */
//    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'User')]
//    private Collection $posts;
//
//    /**
//     * @var Collection<int, React>
//     */
//    #[ORM\OneToMany(targetEntity: React::class, mappedBy: 'User', orphanRemoval: true)]
//    private Collection $reacts;
//
//    public function __construct()
//    {
//        $this->posts = new ArrayCollection();
//        $this->reacts = new ArrayCollection();
//    }


    #[ORM\Column(length: 64, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $rememberMeToken = null;

    public function __construct()
    {
        $this->setStatus("Offline");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): static
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getRememberMeToken(): ?string
    {
        return $this->rememberMeToken;
    }

    public function setRememberMeToken(?string $rememberMeToken): static
    {
        $this->rememberMeToken = $rememberMeToken;

        return $this;
    }
}

