<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private $id;

    #[ORM\OneToMany(mappedBy: 'test', targetEntity: Input::class, orphanRemoval: true)]
    private $inputs;

    #[ORM\OneToOne(inversedBy: 'test', targetEntity: Output::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $output;

    #[ORM\ManyToOne(targetEntity: Challenge::class, inversedBy: 'tests')]
    #[ORM\JoinColumn(nullable: false)]
    private $challenge;

    public function __construct()
    {
        $this->setId(uniqid());
        $this->inputs = new ArrayCollection();
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

    /**
     * @return Collection|Input[]
     */
    public function getInputs(): Collection
    {
        return $this->inputs;
    }

    public function addInput(Input $input): self
    {
        if (!$this->inputs->contains($input)) {
            $this->inputs[] = $input;
            $input->setTest($this);
        }

        return $this;
    }

    public function removeInput(Input $input): self
    {
        if ($this->inputs->removeElement($input)) {
            // set the owning side to null (unless already changed)
            if ($input->getTest() === $this) {
                $input->setTest(null);
            }
        }

        return $this;
    }

    public function getOutput(): ?Output
    {
        return $this->output;
    }

    public function setOutput(Output $output): self
    {
        $this->output = $output;

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
}
