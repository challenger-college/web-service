<?php

namespace App\Entity;

use App\Repository\OutputRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutputRepository::class)]
class Output
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $value;

    #[ORM\OneToOne(mappedBy: 'output', targetEntity: Test::class, cascade: ['persist', 'remove'])]
    private $test;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getTest(): ?Test
    {
        return $this->test;
    }

    public function setTest(Test $test): self
    {
        // set the owning side of the relation if necessary
        if ($test->getOutput() !== $this) {
            $test->setOutput($this);
        }

        $this->test = $test;

        return $this;
    }
}
