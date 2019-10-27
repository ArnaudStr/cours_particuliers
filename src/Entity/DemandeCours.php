<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DemandeCoursRepository")
 */
class DemandeCours
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Seance", inversedBy="demandesCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $seance;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Cours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cours;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Eleve", inversedBy="demandesCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eleve;

    /**
     * @ORM\Column(type="boolean")
     */
    private $repondue;

    public function __construct()
    {
        $this->repondue = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): self
    {
        $this->seance = $seance;

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

    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }

    public function setEleve(?Eleve $eleve): self
    {
        $this->eleve = $eleve;

        return $this;
    }

    public function getRepondue(): ?bool
    {
        return $this->repondue;
    }

    public function setRepondue(bool $repondue): self
    {
        $this->repondue = $repondue;

        return $this;
    }
}
