<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreneauCoursRepository")
 */
class CreneauCours
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prof", inversedBy="creneauCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prof;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="creneauCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $activite;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Creneau", mappedBy="creneauCours",cascade={"persist"}, orphanRemoval=true)
     */
    private $creneaux;

    /**
     * @ORM\Column(type="integer")
     */
    private $tarifHoraire;

    public function __construct()
    {
        $this->creneaux = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): self
    {
        $this->activite = $activite;

        return $this;
    }

 
    /**
     * @return Collection|Creneau[]
     */
    public function getCreneaux(): Collection
    {
        return $this->creneaux;
    }

    public function addCreneau(Creneau $creneau): self
    {
        if (!$this->creneaux->contains($creneau)) {
            $this->creneaux[] = $creneau;
            $creneau->setCreneauCours($this);
        }

        return $this;
    }

    public function removeCreneau(Creneau $creneau): self
    {
        if ($this->creneaux->contains($creneau)) {
            $this->creneaux->removeElement($creneau);
            // set the owning side to null (unless already changed)
            if ($creneau->getCreneauCours() === $this) {
                $creneau->setCreneauCours(null);
            }
        }

        return $this;
    }

    public function getTarifHoraire(): ?int
    {
        return $this->tarifHoraire;
    }

    public function setTarifHoraire(int $tarifHoraire): self
    {
        $this->tarifHoraire = $tarifHoraire;

        return $this;
    }

    /**
     * toString
     * @return string
     */
    public function __toString(){
        return $this->getActivite()->getNom();
    }

}
