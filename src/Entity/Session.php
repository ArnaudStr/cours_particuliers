<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionRepository")
 */
class Session
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateDebut;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Eleve", inversedBy="sessions")
     */
    private $eleve;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cours", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $cours;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prof", inversedBy="sessions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prof;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DemandeCours", mappedBy="session", orphanRemoval=true)
     */
    private $demandesCours;

    public function __construct()
    {
        $this->validee = false;
        $this->demandesCours = new ArrayCollection();
        // $this->cours=null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }

    public function setEleve(?Eleve $eleve): self
    {
        $this->eleve = $eleve;

        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): self
    {
        $this->cours = $cours;

        return $this;
    }

    public function getProf(): ?Prof
    {
        return $this->prof;
    }

    public function setProf(?Prof $prof): self
    {
        $this->prof = $prof;

        return $this;
    }

    /**
     * @return Collection|DemandeCours[]
     */
    public function getDemandesCours(): Collection
    {
        return $this->demandesCours;
    }

    public function addDemandesCour(DemandeCours $demandesCour): self
    {
        if (!$this->demandesCours->contains($demandesCour)) {
            $this->demandesCours[] = $demandesCour;
            $demandesCour->setSession($this);
        }

        return $this;
    }

    public function removeDemandesCour(DemandeCours $demandesCour): self
    {
        if ($this->demandesCours->contains($demandesCour)) {
            $this->demandesCours->removeElement($demandesCour);
            // set the owning side to null (unless already changed)
            if ($demandesCour->getSession() === $this) {
                $demandesCour->setSession(null);
            }
        }

        return $this;
    }

}
