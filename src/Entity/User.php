<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User extends LoggableEntity implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Column(length: 180)]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, IgAccount>
     */
    #[ORM\OneToMany(targetEntity: IgAccount::class, mappedBy: 'User')]
    private Collection $igAccounts;

    public function __construct()
    {
        $this->igAccounts = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, IgAccount>
     */
    public function getIgAccounts(): Collection
    {
        return $this->igAccounts;
    }

    public function addIgAccount(IgAccount $igAccount): static
    {
        if (!$this->igAccounts->contains($igAccount)) {
            $this->igAccounts->add($igAccount);
            $igAccount->setUser($this);
        }

        return $this;
    }

    public function removeIgAccount(IgAccount $igAccount): static
    {
        if ($this->igAccounts->removeElement($igAccount)) {
            // set the owning side to null (unless already changed)
            if ($igAccount->getUser() === $this) {
                $igAccount->setUser(null);
            }
        }

        return $this;
    }
}
