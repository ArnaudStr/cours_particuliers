<?php

namespace App\Entity;

use DateTime;
use DateTimeZone;
use App\Entity\Avis;
use App\Entity\Message;
use App\Entity\Seance;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EleveRepository")
 * @UniqueEntity("email", message="Email déjà utilisé")
 */
class Eleve implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Veuillez entrer un email")
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="eleve", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Avis", mappedBy="eleve", orphanRemoval=true)
     */
    private $avis;

    /**
     * @ORM\Column(type="string")
     */
    private $pictureFilename;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Seance", mappedBy="eleve", orphanRemoval=true)
     */
    private $seances;

    /**
     * @var string le token qui servira lors de l'oubli de mot de passe
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $aConfirme;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DemandeCours", mappedBy="eleve", orphanRemoval=true)
     */
    private $demandesCours;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Cours", mappedBy="eleves")
     */
    private $cours;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $tokenExpire;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbMsgNonLus;

    public function __construct()
    {
        $this->aConfirme = false;
        $this->messages = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->dateCreation = new DateTime('now',new DateTimeZone('Europe/Paris'));
        $this->seances = new ArrayCollection();
        $this->demandesCours = new ArrayCollection();
        $this->cours = new ArrayCollection();
        $this->nbMsgNonLus = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

        /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setEleve($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getEleve() === $this) {
                $message->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Avis[]
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
            $avi->setEleve($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->contains($avi)) {
            $this->avis->removeElement($avi);
            // set the owning side to null (unless already changed)
            if ($avi->getEleve() === $this) {
                $avi->setEleve(null);
            }
        }

        return $this;
    }

    public function getPictureFilename(): ?string
    {
        if(!$this->pictureFilename){
            return "default.jpg";
        }
        return $this->pictureFilename;
    }

    public function setPictureFilename(?string $pictureFilename): self
    {
        $this->pictureFilename = $pictureFilename;

        return $this;
    }  
    
    /**
     * @return Collection|Seance[]
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): self
    {
        if (!$this->seances->contains($seance)) {
            $this->seances[] = $seance;
            $seance->setEleve($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): self
    {
        if ($this->seances->contains($seance)) {
            $this->seances->removeElement($seance);
            // set the owning side to null (unless already changed)
            if ($seance->getEleve() === $this) {
                $seance->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }
 
    /**
     * @param string $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getAConfirme(): ?bool
    {
        return $this->aConfirme;
    }

    public function setAConfirme(bool $aConfirme): self
    {
        $this->aConfirme = $aConfirme;

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
            $demandesCour->setEleve($this);
        }

        return $this;
    }

    public function removeDemandesCour(DemandeCours $demandesCour): self
    {
        if ($this->demandesCours->contains($demandesCour)) {
            $this->demandesCours->removeElement($demandesCour);
            // set the owning side to null (unless already changed)
            if ($demandesCour->getEleve() === $this) {
                $demandesCour->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * toString
     * @return string
     */
    public function __toString(){
        return ucfirst($this->getPrenom()).' '.strtoupper($this->getNom());
    }

    /**
     * @return Collection|Cours[]
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours[] = $cour;
            $cour->addElefe($this);
        }

        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->contains($cour)) {
            $this->cours->removeElement($cour);
            $cour->removeElefe($this);
        }

        return $this;
    }

    public function getTokenExpire(): ?\DateTimeInterface
    {
        return $this->tokenExpire;
    }

    public function setTokenExpire(?\DateTimeInterface $tokenExpire): self
    {
        $this->tokenExpire = $tokenExpire;

        return $this;
    }

    public function getNbMsgNonLus(): ?int
    {
        return $this->nbMsgNonLus;
    }

    public function setNbMsgNonLus(int $nbMsgNonLus): self
    {
        $this->nbMsgNonLus = $nbMsgNonLus;

        return $this;
    }
}
