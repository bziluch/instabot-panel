<?php

namespace App\Entity;

use App\Repository\AppRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppRequestRepository::class)]
class AppRequest
{
    const TYPE_IG_LOGIN_REQUEST = 0;
    const TYPE_2FA_CODE = 1;

    const DIR_PANEL_TO_APP = 0;
    const DIR_APP_TO_PANEL = 1;

    const STATUS_PENDING = 0;
    const STATUS_ANSWERED = 1;
    const STATUS_CLOSED = 2;
    const STATUS_CANCELLED = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?IgAccount $account = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $type = self::TYPE_IG_LOGIN_REQUEST;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $directory = self::DIR_PANEL_TO_APP;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $response = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $responseDate = null;

    public function __construct()
    {
        $this->setCreateDate(new \DateTime('now'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): ?IgAccount
    {
        return $this->account;
    }

    public function setAccount(?IgAccount $account): static
    {
        $this->account = $account;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDirectory(): ?int
    {
        return $this->directory;
    }

    public function setDirectory(int $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): static
    {
        $this->response = $response;

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

    public function getCreateDate(): ?\DateTimeInterface
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTimeInterface $createDate): static
    {
        $this->createDate = $createDate;

        return $this;
    }

    public function getResponseDate(): ?\DateTimeInterface
    {
        return $this->responseDate;
    }

    public function setResponseDate(?\DateTimeInterface $responseDate): static
    {
        $this->responseDate = $responseDate;

        return $this;
    }
}
