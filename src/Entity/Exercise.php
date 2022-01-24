<?php

namespace App\Entity;

use App\Repository\ExerciseRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
class Exercise
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'exercises')]
    #[ORM\JoinColumn(nullable: false)]
    private $author;

    #[ORM\ManyToOne(targetEntity: Challenge::class, inversedBy: 'exercises')]
    #[ORM\JoinColumn(nullable: false)]
    private $challenge;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $validated;

    #[ORM\OneToMany(mappedBy: 'exercise', targetEntity: Result::class, orphanRemoval: true, cascade: ['persist'])]
    private $results;

    #[ORM\Column(type: 'datetime')]
    private $createDate;

    #[ORM\Column(type: 'datetime')]
    private $updateDate;

    public function __construct()
    {
        $this->setId(uniqid());
        $this->setCreateDate($datetime = new DateTime());
        $this->setUpdateDate($datetime);
        $this->results = new ArrayCollection([(new Result())->setExercise($this)]);
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getChallenge(): ?Challenge
    {
        return $this->challenge;
    }

    public function setChallenge(?Challenge $challenge): self
    {
        $this->challenge = $challenge;

        return $this;
    }

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(?bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * @return Collection|Result[]
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(Result $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results[] = $result;
            $result->setExercise($this);
        }

        return $this;
    }

    public function removeResult(Result $result): self
    {
        if ($this->results->removeElement($result)) {
            // set the owning side to null (unless already changed)
            if ($result->getExercise() === $this) {
                $result->setExercise(null);
            }
        }

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
            'content' => $this->getContent(),
            'author' => $this->getAuthor()->array(),
            'challenge' => $this->getChallenge()->array(),
            'validated' => $this->getValidated(),
            'results' => array_map(function ($result) {
                    return $result->array();
                }, iterator_to_array($this->getResults())
            ),
            'token' => $this->getResults()?->last()?->getId(),
            'createDate' => $this->getCreateDate(),
            'updateDate' => $this->getUpdateDate(),
        ];
    }
}
