<?php

namespace App\Entity;

use App\Repository\IgAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IgAccountRepository::class)]
class IgAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'igAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;
    private ?string $usernameDecrypted = null;
    private bool $usernameChanged = false;

    #[ORM\Column(length: 255)]
    private ?string $password = null;
    private bool $passwordChanged = false;

    #[ORM\Column(length: 255)]
    private ?string $linkedAccount = null;
    private ?string $linkedAccountDecrypted = null;
    private bool $linkedAccountChanged = false;

    #[ORM\Column]
    private ?bool $active = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->usernameDecrypted;
    }

    public function setUsername(string $username, bool $enc = false): static
    {
        if ($enc) {
            $this->username = $username;
        } else {
            $this->usernameDecrypted = $username;
            $this->usernameChanged = true;
        }

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

    public function getLinkedAccount(): ?string
    {
        return $this->linkedAccountDecrypted;
    }

    public function setLinkedAccount(string $linkedAccount, bool $enc = false): static
    {
        if ($enc) {
            $this->linkedAccount = $linkedAccount;
        } else {
            $this->linkedAccountDecrypted = $linkedAccount;
            $this->linkedAccountChanged = true;
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function hasLinkedAccountChanged(): bool
    {
        return $this->linkedAccountChanged;
    }

    public function hasPasswordChanged(): bool
    {
        return $this->passwordChanged;
    }

    public function hasUsernameChanged(): bool
    {
        return $this->usernameChanged;
    }
}
