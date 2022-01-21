<?php

namespace App\Entity;

use App\Repository\ResultRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
class Result
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Exercise::class, inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private $exercise;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $time;

    #[ORM\Column(type: 'datetime')]
    private $createDate;

    #[ORM\OneToMany(mappedBy: 'result', targetEntity: Error::class, orphanRemoval: true)]
    private $errors;

    #[ORM\Column(type: 'text', nullable: true)]
    private $output;

    public function __construct()
    {
        $this->setId(uniqid());
        $this->setCreateDate(new DateTime());
        $this->errors = new ArrayCollection();
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

    public function getExercise(): ?Exercise
    {
        return $this->exercise;
    }

    public function setExercise(?Exercise $exercise): self
    {
        $this->exercise = $exercise;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getCreateDate(): ?DateTime
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTime $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * @return Collection|Error[]
     */
    public function getErrors(): Collection
    {
        return $this->errors;
    }

    public function addError(Error $error): self
    {
        if (!$this->errors->contains($error)) {
            $this->errors[] = $error;
            $error->setResult($this);
        }

        return $this;
    }

    public function removeError(Error $error): self
    {
        if ($this->errors->removeElement($error)) {
            // set the owning side to null (unless already changed)
            if ($error->getResult() === $this) {
                $error->setResult(null);
            }
        }

        return $this;
    }

    public function array(): array
    {
        foreach ($this->getErrors() ?? [] as $error) {
            $errors[] = $error;
        }

        return [
            'id' => $this->getId(),
            'time' => $this->getTime(),
            'createDate' => $this->getCreateDate(),
            'errors' => $errors ?? [],
        ];
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): self
    {
        $this->output = $output;

        return $this;
    }
}
