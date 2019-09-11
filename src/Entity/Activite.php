<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActiviteRepository")
 */
class Activite
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Categorie", inversedBy="activites")
     * @ORM\JoinColumn(nullable=false)
     */
    private $categorie;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Session", mappedBy="activite", orphanRemoval=true)
     */
    private $sessions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CreneauCours", mappedBy="activite", orphanRemoval=true)
     */
    private $creneauxCours;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->creneauCours = new ArrayCollection();
        $this->creneauxCours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }


    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setActivite($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->contains($session)) {
            $this->sessions->removeElement($session);
            // set the owning side to null (unless already changed)
            if ($session->getActivite() === $this) {
                $session->setActivite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CreneauCours[]
     */
    public function getCreneauxCours(): Collection
    {
        return $this->creneauxCours;
    }

    public function addCreneauCours(CreneauCours $creneauCours): self
    {
        if (!$this->creneauxCours->contains($creneauCours)) {
            $this->creneauxCours[] = $creneauCours;
            $creneauCours->setActivite($this);
        }

        return $this;
    }

    public function removeCreneauCours(CreneauCours $creneauCours): self
    {
        if ($this->creneauxCours->contains($creneauCours)) {
            $this->creneauxCours->removeElement($creneauCours);
            // set the owning side to null (unless already changed)
            if ($creneauCours->getActivite() === $this) {
                $creneauCours->setActivite(null);
            }
        }

        return $this;
    }

    public function addCreneauxCour(CreneauCours $creneauxCour): self
    {
        if (!$this->creneauxCours->contains($creneauxCour)) {
            $this->creneauxCours[] = $creneauxCour;
            $creneauxCour->setActivite($this);
        }

        return $this;
    }

    public function removeCreneauxCour(CreneauCours $creneauxCour): self
    {
        if ($this->creneauxCours->contains($creneauxCour)) {
            $this->creneauxCours->removeElement($creneauxCour);
            // set the owning side to null (unless already changed)
            if ($creneauxCour->getActivite() === $this) {
                $creneauxCour->setActivite(null);
            }
        }

        return $this;
    }
}
