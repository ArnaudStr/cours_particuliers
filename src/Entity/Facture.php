<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FactureRepository")
 */
class Facture
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
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionCours", mappedBy="facture")
     */
    private $sessionsCours;

    public function __construct()
    {
        $this->sessionsCours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * @return Collection|SessionCours[]
     */
    public function getSessionsCours(): Collection
    {
        return $this->sessionsCours;
    }

    public function addSessionsCour(SessionCours $sessionsCour): self
    {
        if (!$this->sessionsCours->contains($sessionsCour)) {
            $this->sessionsCours[] = $sessionsCour;
            $sessionsCour->setFacture($this);
        }

        return $this;
    }

    public function removeSessionsCour(SessionCours $sessionsCour): self
    {
        if ($this->sessionsCours->contains($sessionsCour)) {
            $this->sessionsCours->removeElement($sessionsCour);
            // set the owning side to null (unless already changed)
            if ($sessionsCour->getFacture() === $this) {
                $sessionsCour->setFacture(null);
            }
        }

        return $this;
    }
}
