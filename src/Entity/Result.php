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

    #[ORM\ManyToOne(targetEntity: Exercise::class, inversedBy: 'results', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private $exercise;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $time;

    #[ORM\OneToMany(mappedBy: 'result', targetEntity: Error::class, orphanRemoval: true)]
    private $errors;

    #[ORM\Column(type: 'text', nullable: true)]
    private $output;

    #[ORM\Column(type: 'datetime')]
    private $createDate;

    #[ORM\Column(type: 'datetime')]
    private $updateDate;

    public function __construct()
    {
        $this->setId(uniqid());
        $this->setCreateDate($datetime = new DateTime());
        $this->setUpdateDate($datetime);
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

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): self
    {
        $this->output = $output;

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

    public function getUpdateDate(): ?DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(DateTime $updateDate): self
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function array(): array
    {
        return [
            'id' => $this->getId(),
            'output' => $this->getOutput(),
            'time' => $this->getTime(),
            'errors' => array_map(function (Error $error): array {
                return $error->array();
            }, iterator_to_array($this->getErrors())),
            'createDate' => $this->getCreateDate(),
            'updateDate' => $this->getUpdateDate(),
        ];
    }
}
