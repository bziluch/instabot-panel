<?php

namespace App\Entity;

use App\Repository\IgAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IgAccountRepository::class)]
class IgAccount extends LoggableEntity
{

    const STATUS_UNVERIFIED = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_UNSUCCESSFUL = 2;
    const STATUS_CONNECTION_BROKEN = 3;

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

    /**
     * @var Collection<int, Schedule>
     */
    #[ORM\OneToMany(targetEntity: Schedule::class, mappedBy: 'igAccount')]
    private Collection $schedules;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = self::STATUS_UNVERIFIED;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $session = null;

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
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
        return $this->usernameDecrypted ?? $this->username;
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
        $this->passwordChanged = true;

        return $this;
    }

    public function getLinkedAccount(): ?string
    {
        return $this->linkedAccountDecrypted ?? $this->linkedAccount;
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

    /**
     * @return Collection<int, Schedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedule $schedule): static
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setIgAccount($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): static
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getIgAccount() === $this) {
                $schedule->setIgAccount(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(?string $session): static
    {
        $this->session = $session;

        return $this;
    }
}
