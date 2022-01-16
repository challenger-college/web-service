<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'text')]
    private $description;

    #[ORM\Column(type: 'text', nullable: true)]
    private $constraints;

    #[ORM\Column(type: 'integer')]
    private $timeout;

    #[ORM\Column(type: 'string', length: 255)]
    private $function_name;

    #[ORM\Column(type: 'datetime')]
    private $createDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updateDate;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'challenges')]
    #[ORM\JoinColumn(nullable: false)]
    private $author;

    #[ORM\OneToMany(mappedBy: 'challenge', targetEntity: Test::class, orphanRemoval: true)]
    private $tests;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $validity;

    #[ORM\OneToMany(mappedBy: 'challenge', targetEntity: Exercice::class, orphanRemoval: true)]
    private $exercices;

    public function __construct()
    {
        $this->setId(uniqid());
        $this->tests = new ArrayCollection();
        $this->exercices = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getConstraints(): ?string
    {
        return $this->constraints;
    }

    public function setConstraints(?string $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getFunctionName(): ?string
    {
        return $this->function_name;
    }

    public function setFunctionName(string $function_name): self
    {
        $this->function_name = $function_name;

        return $this;
    }

    public function getCreateDate(): ?\DateTime
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTime $createDate): self
    {
        $this->createDate = $createDate;

        return $this;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(?\DateTime $updateDate): self
    {
        $this->updateDate = $updateDate;

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

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->tests->contains($test)) {
            $this->tests[] = $test;
            $test->setChallenge($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getChallenge() === $this) {
                $test->setChallenge(null);
            }
        }

        return $this;
    }

    public function getValidity(): ?bool
    {
        return $this->validity;
    }

    public function setValidity(?bool $validity): self
    {
        $this->validity = $validity;

        return $this;
    }

    /**
     * @return Collection|Exercice[]
     */
    public function getExercices(): Collection
    {
        return $this->exercices;
    }

    public function addExercice(Exercice $exercice): self
    {
        if (!$this->exercices->contains($exercice)) {
            $this->exercices[] = $exercice;
            $exercice->setChallenge($this);
        }

        return $this;
    }

    public function removeExercice(Exercice $exercice): self
    {
        if ($this->exercices->removeElement($exercice)) {
            // set the owning side to null (unless already changed)
            if ($exercice->getChallenge() === $this) {
                $exercice->setChallenge(null);
            }
        }

        return $this;
    }

    public function array(): array {
        foreach ($this->getTests() ?? [] as $test):
            foreach ($test->getInputs() as $input):
                $inputs[] = ['name' => $input->getName(), 'value' => $input->getValue()];
            endforeach;
            $tests[] = ['inputs' => $inputs, 'output' => $test->getOutput()->getValue()];
        endforeach;
        
        return [
            'id' => $this->getId(),
            'function_name' => $this->getFunctionName(),
            'timeout' => $this->getTimeout(),
            'tests' => $tests,
        ];    
    }
}
