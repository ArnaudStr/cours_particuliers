<?php

namespace App\Entity;

use App\Entity\SessionCours;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PrixActiviteRepository")
 */
class PrixActivite
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $prix;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prof", inversedBy="prixActivites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prof;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="prixActivites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $activite;

    public function __construct()
    {
        $this->sessionCours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;

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
     * @return Collection|SessionCours[]
     */
    public function getSessionCours(): Collection
    {
        return $this->sessionCours;
    }

    public function addSessionCour(SessionCours $sessionCour): self
    {
        if (!$this->sessionCours->contains($sessionCour)) {
            $this->sessionCours[] = $sessionCour;
            $sessionCour->setPrixActivite($this);
        }

        return $this;
    }

    public function removeSessionCour(SessionCours $sessionCour): self
    {
        if ($this->sessionCours->contains($sessionCour)) {
            $this->sessionCours->removeElement($sessionCour);
            // set the owning side to null (unless already changed)
            if ($sessionCour->getPrixActivite() === $this) {
                $sessionCour->setPrixActivite(null);
            }
        }

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): self
    {
        $this->activite = $activite;

        return $this;
    }
}
