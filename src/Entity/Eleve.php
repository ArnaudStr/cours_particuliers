<?php

namespace App\Entity;

use DateTime;
use DateTimeZone;
use App\Entity\Avis;
use App\Entity\Message;
use App\Entity\SessionCours;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\EleveRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
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
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

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
     * @ORM\Column(type="string", length=255)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
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
     * @ORM\OneToMany(targetEntity="App\Entity\SessionCours", mappedBy="eleve", orphanRemoval=true)
     */
    private $sessionsCours;

    /**
     * @ORM\Column(type="string")
     * @Assert\File(
     *      mimeTypes={ "image/jpg", "image/jpeg", "image/png" })
     *      maxSize = "1M",
     *      mimeTypesMessage = "Image non valide",
     *      maxSizeMessage = "L'image est trop lourde, taille max : {{ size }}"
     */
    private $pictureFilename;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->sessionsCours = new ArrayCollection();
        $this->dateCreation = new DateTime('now',new DateTimeZone('Europe/Paris'));
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
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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
            $sessionsCour->setEleve($this);
        }

        return $this;
    }

    public function removeSessionsCour(SessionCours $sessionsCour): self
    {
        if ($this->sessionsCours->contains($sessionsCour)) {
            $this->sessionsCours->removeElement($sessionsCour);
            // set the owning side to null (unless already changed)
            if ($sessionsCour->getEleve() === $this) {
                $sessionsCour->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * toString
     * @return string
     */
    public function __toString(){
        return $this->getPrenom().' '.$this->getNom();
    }

    public function getPictureFilename(): ?string
    {
        return $this->pictureFilename;
    }

    public function setPictureFilename(?string $pictureFilename): self
    {
        if (!is_null($pictureFilename)) {
            $this->pictureFilename = $pictureFilename;
        }

        return $this;
    }
}
