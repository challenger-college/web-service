<?php

namespace App\Entity;

use App\Repository\ErrorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ErrorRepository::class)]
class Error
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Result::class, inversedBy: 'errors', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $result;

    #[ORM\Column(type: 'text')]
    private $message;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $line_number;

    public function __construct()
    {
        $this->setId(uniqid());
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getResult(): ?Result
    {
        return $this->result;
    }

    public function setResult(?Result $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function array(): array
    {
        return [
            'id' => $this->getId(),
            'message' => $this->getMessage(),
        ];
    }

    public function getLineNumber(): ?int
    {
        return $this->line_number;
    }

    public function setLineNumber(?int $line_number): self
    {
        $this->line_number = $line_number;

        return $this;
    }
}
