<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use DateTime;
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

    #[ORM\OneToMany(mappedBy: 'challenge', targetEntity: Exercise::class, orphanRemoval: true)]
    private $exercises;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private $image;

    #[ORM\Column(type: 'text', nullable: true)]
    private $template;

    public function __construct()
    {
        $this->setId(uniqid());
        $this->setCreateDate(new DateTime());
        $this->tests = new ArrayCollection();
        $this->exercises = new ArrayCollection();
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

    public function setUpdateDate(?DateTime $updateDate): self
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
     * @return Collection|Exercise[]
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }

    public function addExercise(Exercise $exercise): self
    {
        if (!$this->exercises->contains($exercise)) {
            $this->exercises[] = $exercise;
            $exercise->setChallenge($this);
        }

        return $this;
    }

    public function removeExercise(Exercise $exercise): self
    {
        if ($this->exercises->removeElement($exercise)) {
            // set the owning side to null (unless already changed)
            if ($exercise->getChallenge() === $this) {
                $exercise->setChallenge(null);
            }
        }

        return $this;
    }

    public function array(): array
    {
        foreach ($this->getTests() ?? [] as $test) {
            $inputs = [];
            foreach ($test->getInputs() as $input) {
                $inputs[] = ['name' => $input->getName(), 'value' => $input->getValue()];
            }
            $tests[] = ['inputs' => $inputs, 'output' => $test->getOutput()->getValue()];
        }

        return [
            'id' => $this->getId(),
            'function_name' => $this->getFunctionName(),
            'timeout' => $this->getTimeout(),
            'tests' => $tests,
        ];
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }
}
