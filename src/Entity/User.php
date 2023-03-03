<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("users")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "vous devez mettre votre nom !")]
    #[Assert\Length( min: 3, minMessage: 'Le nom doit avoir au minimum 3 caracteres !',)]
    #[Groups("users")]

    private ?string $nom = null;

    #[Groups("users")]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "vous devez mettre votre prenom !")]
    #[Assert\Length( min: 3, minMessage: 'Le prenom doit avoir au minimum 3 caracteres !',)]
    private ?string $prenom = null;


    #[ORM\Column]
    #[Groups("users")]
    #[Assert\NotBlank(message: "vous devez mettre votre telephone !")]
    #[Assert\Range(
        notInRangeMessage: 'Le numéro de telephone doit etre de 8 chiffres !',
        min: 10000000,
        max: 99999999,

    )]
    private ?int $tel = null;

    #[ORM\Column(length: 255)]
    #[Groups("users")]
    #[Assert\NotBlank(message: "vous devez mettre votre adresse !")]
    #[Assert\Length( min: 3, minMessage: 'Adress doit avoir au minimum 3 caracteres !',)]

    private ?string $adress = null;

    #[ORM\Column(length: 150 , unique: true)]
    #[Groups("users")]
    #[Assert\Email(message: "L'Email {{ value }} n'est pas un Email valide !")]
    #[Assert\NotBlank(message: "vous devez mettre votre Email !")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups("users")]
    #[Assert\NotBlank(message: "vous devez mettre votre mot de passe!!!")]
    #[Assert\Length( min: 8, minMessage: 'Mode de passe doit avoir au minimum 8 caracteres !',)]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    #[Groups("users")]
    private $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups("users")]
    private ?string $image  ="default.png";

    public function getImage(): ?string
    {
        return $this->image;
    }
    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTel(): ?int
    {
        return $this->tel;
    }

    public function setTel(int $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getUsername(): string
    {
        return (string) $this->email;
    }

}

