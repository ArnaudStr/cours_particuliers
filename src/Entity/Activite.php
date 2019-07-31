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
     * @ORM\OneToMany(targetEntity="App\Entity\PrixActivite", mappedBy="activite", orphanRemoval=true)
     */
    private $prixActivites;

    public function __construct()
    {
        $this->prixActivites = new ArrayCollection();
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
     * @return Collection|PrixActivite[]
     */
    public function getPrixActivites(): Collection
    {
        return $this->prixActivites;
    }

    public function addPrixActivite(PrixActivite $prixActivite): self
    {
        if (!$this->prixActivites->contains($prixActivite)) {
            $this->prixActivites[] = $prixActivite;
            $prixActivite->setActivite($this);
        }

        return $this;
    }

    public function removePrixActivite(PrixActivite $prixActivite): self
    {
        if ($this->prixActivites->contains($prixActivite)) {
            $this->prixActivites->removeElement($prixActivite);
            // set the owning side to null (unless already changed)
            if ($prixActivite->getActivite() === $this) {
                $prixActivite->setActivite(null);
            }
        }

        return $this;
    }
}
