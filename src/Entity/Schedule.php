<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column]
    private ?int $fulfilled = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?IgAccount $igAccount = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    /**
     * @var Collection<int, AccostedAccounts>
     */
    #[ORM\OneToMany(targetEntity: AccostedAccounts::class, mappedBy: 'schedule')]
    private Collection $accostedAccounts;

    public function __construct()
    {
        $this->accostedAccounts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFulfilled(): ?int
    {
        return $this->fulfilled;
    }

    public function setFulfilled(int $fulfilled): static
    {
        $this->fulfilled = $fulfilled;

        return $this;
    }

    public function getIgAccount(): ?IgAccount
    {
        return $this->igAccount;
    }

    public function setIgAccount(?IgAccount $igAccount): static
    {
        $this->igAccount = $igAccount;

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

    /**
     * @return Collection<int, AccostedAccounts>
     */
    public function getAccostedAccounts(): Collection
    {
        return $this->accostedAccounts;
    }

    public function addAccostedAccount(AccostedAccounts $accostedAccount): static
    {
        if (!$this->accostedAccounts->contains($accostedAccount)) {
            $this->accostedAccounts->add($accostedAccount);
            $accostedAccount->setSchedule($this);
        }

        return $this;
    }

    public function removeAccostedAccount(AccostedAccounts $accostedAccount): static
    {
        if ($this->accostedAccounts->removeElement($accostedAccount)) {
            // set the owning side to null (unless already changed)
            if ($accostedAccount->getSchedule() === $this) {
                $accostedAccount->setSchedule(null);
            }
        }

        return $this;
    }
}
