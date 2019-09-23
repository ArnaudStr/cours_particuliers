<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CoursRepository")
 */
class Cours
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prof", inversedBy="coursS")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prof;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="coursS")
     * @ORM\JoinColumn(nullable=false)
     */
    private $activite;

    /**
     * @ORM\Column(type="integer")
     */
    private $tarifHoraire;

    /**
     * @ORM\Column(type="boolean")
     */
    private $webcam;

    /**
     * @ORM\Column(type="boolean")
     */
    private $domicile;

    /**
     * @ORM\Column(type="boolean")
     */
    private $chezEleve;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Session", mappedBy="cours", orphanRemoval=true)
     */
    private $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
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

    public function getWebcam(): ?bool
    {
        return $this->webcam;
    }

    public function setWebcam(bool $webcam): self
    {
        $this->webcam = $webcam;

        return $this;
    }

    public function getDomicile(): ?bool
    {
        return $this->domicile;
    }

    public function setDomicile(bool $domicile): self
    {
        $this->domicile = $domicile;

        return $this;
    }

    public function getChezEleve(): ?bool
    {
        return $this->chezEleve;
    }

    public function setChezEleve(bool $chezEleve): self
    {
        $this->chezEleve = $chezEleve;

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
            $session->setCours($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->contains($session)) {
            $this->sessions->removeElement($session);
            // set the owning side to null (unless already changed)
            if ($session->getCours() === $this) {
                $session->setCours(null);
            }
        }

        return $this;
    }

}
