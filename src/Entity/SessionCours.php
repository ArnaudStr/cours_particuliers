<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionCoursRepository")
 */
class SessionCours
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
    private $duree;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Facture", inversedBy="sessionsCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PrixActivite", inversedBy="sessionCours")
     */
    private $prixActivite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prof", inversedBy="sessionsCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prof;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Eleve", inversedBy="sessionsCours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $eleve;

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

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): self
    {
        $this->facture = $facture;

        return $this;
    }

    public function getPrixActivite(): ?PrixActivite
    {
        return $this->prixActivite;
    }

    public function setPrixActivite(?PrixActivite $prixActivite): self
    {
        $this->prixActivite = $prixActivite;

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

    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }

    public function setEleve(?Eleve $eleve): self
    {
        $this->eleve = $eleve;

        return $this;
    }
}
