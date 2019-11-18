<?php

namespace App\Entity;

use DateTime;
use DateTimeZone;
use App\Entity\Avis;
use App\Entity\Cours;
use App\Entity\Message;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfRepository")
 * @UniqueEntity("email", message="Email déjà utilisé")
 */
class Prof implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue() 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="prof", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Avis", mappedBy="prof", orphanRemoval=true)
     */
    private $avis;

    /**
     * @ORM\Column(type="string")
     */
    private $pictureFilename;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Cours", mappedBy="prof", orphanRemoval=true)
     */
    private $coursS;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $notes = [];

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
     * @ORM\OneToMany(targetEntity="App\Entity\Seance", mappedBy="prof", orphanRemoval=true)
     */
    private $seances;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $noteMoyenne;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenExpire;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $disponibilites = [];

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
        $this->coursS = new ArrayCollection();
        $this->seances = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

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
            $message->setProf($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getProf() === $this) {
                $message->setProf(null);
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
            $avi->setProf($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->contains($avi)) {
            $this->avis->removeElement($avi);
            // set the owning side to null (unless already changed)
            if ($avi->getProf() === $this) {
                $avi->setProf(null);
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

    // public function setPictureFilename(?string $pictureFilename): self
    public function setPictureFilename($pictureFilename)
    {
        $this->pictureFilename = $pictureFilename;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Cours[]
     */
    public function getCoursS(): Collection
    {
        return $this->coursS;
    }

    public function addCours(Cours $cours): self
    {
        if (!$this->coursS->contains($cours)) {
            $this->coursS[] = $cours;
            $cours->setProf($this);
        }

        return $this;
    }

    public function removeCours(Cours $cours): self
    {
        if ($this->coursS->contains($cours)) {
            $this->coursS->removeElement($cours);
            // set the owning side to null (unless already changed)
            if ($cours->getProf() === $this) {
                $cours->setProf(null);
            }
        }

        return $this;
    }
    
    public function getNotes(): ?array
    {
        return $this->notes;
    }

    public function setNotes(?array $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function addNote(Float $note): self
    {
        if ($note<=5 && $note>=0) {
            $this->notes[] = $note;
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
            $seance->setProf($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): self
    {
        if ($this->seances->contains($seance)) {
            $this->seances->removeElement($seance);
            // set the owning side to null (unless already changed)
            if ($seance->getProf() === $this) {
                $seance->setProf(null);
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

    public function getNoteMoyenne(): ?float
    {
        return $this->noteMoyenne;
    }

    public function setNoteMoyenne(?float $noteMoyenne): self
    {
        $this->noteMoyenne = $noteMoyenne;

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

    public function getDisponibilites(): ?array
    {
        return $this->disponibilites;
    }

    public function setDisponibilites(?array $disponibilites): self
    {
        $this->disponibilites = $disponibilites;

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
