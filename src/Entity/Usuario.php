<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitud = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitud = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $ultima_conexion = null;

    /**
     * @var Collection<int, Chat>
     */
    #[ORM\OneToMany(targetEntity: Chat::class, mappedBy: 'creador')]
    private Collection $chats;

    /**
     * @var Collection<int, Mensaje>
     */
    #[ORM\OneToMany(targetEntity: Mensaje::class, mappedBy: 'autor')]
    private Collection $mensajes;

    /**
     * @var Collection<int, Bloqueo>
     */
    #[ORM\OneToMany(targetEntity: Bloqueo::class, mappedBy: 'bloqueador')]
    private Collection $bloqueador;

    /**
     * @var Collection<int, Bloqueo>
     */
    #[ORM\OneToMany(targetEntity: Bloqueo::class, mappedBy: 'bloqueado')]
    private Collection $bloqueos;

    /**
     * @var Collection<int, Invitacion>
     */
    #[ORM\OneToMany(targetEntity: Invitacion::class, mappedBy: 'remitente')]
    private Collection $invitacions;

    /**
     * @var Collection<int, Invitacion>
     */
    #[ORM\OneToMany(targetEntity: Invitacion::class, mappedBy: 'destinatario')]
    private Collection $destination;

    /**
     * @var Collection<int, Invitacion>
     */
    #[ORM\OneToMany(targetEntity: Invitacion::class, mappedBy: 'chat')]
    private Collection $Invitacion_chat;

    /**
     * @var Collection<int, Invitacion>
     */
    #[ORM\OneToMany(targetEntity: Invitacion::class, mappedBy: 'remitente')]
    private Collection $Inv_remitente;

    /**
     * @var Collection<int, Invitacion>
     */
    #[ORM\OneToMany(targetEntity: Invitacion::class, mappedBy: 'destinatario')]
    private Collection $Inv_destinatario;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    public function __construct()
    {
        $this->chats = new ArrayCollection();
        $this->mensajes = new ArrayCollection();
        $this->bloqueador = new ArrayCollection();
        $this->bloqueos = new ArrayCollection();
        $this->invitacions = new ArrayCollection();
        $this->destination = new ArrayCollection();
        $this->Invitacion_chat = new ArrayCollection();
        $this->Inv_remitente = new ArrayCollection();
        $this->Inv_destinatario = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
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

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getLatitud(): ?float
    {
        return $this->latitud;
    }

    public function setLatitud(?float $latitud): static
    {
        $this->latitud = $latitud;

        return $this;
    }

    public function getLongitud(): ?float
    {
        return $this->longitud;
    }

    public function setLongitud(?float $longitud): static
    {
        $this->longitud = $longitud;

        return $this;
    }

    public function getUltimaConexion(): ?\DateTimeImmutable
    {
        return $this->ultima_conexion;
    }

    public function setUltimaConexion(?\DateTimeImmutable $ultima_conexion): static
    {
        $this->ultima_conexion = $ultima_conexion;

        return $this;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): static
    {
        if (!$this->chats->contains($chat)) {
            $this->chats->add($chat);
            $chat->setCreador($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): static
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getCreador() === $this) {
                $chat->setCreador(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Mensaje>
     */
    public function getMensajes(): Collection
    {
        return $this->mensajes;
    }

    public function addMensaje(Mensaje $mensaje): static
    {
        if (!$this->mensajes->contains($mensaje)) {
            $this->mensajes->add($mensaje);
            $mensaje->setAutor($this);
        }

        return $this;
    }

    public function removeMensaje(Mensaje $mensaje): static
    {
        if ($this->mensajes->removeElement($mensaje)) {
            // set the owning side to null (unless already changed)
            if ($mensaje->getAutor() === $this) {
                $mensaje->setAutor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bloqueo>
     */
    public function getBloqueador(): Collection
    {
        return $this->bloqueador;
    }

    public function addBloqueador(Bloqueo $bloqueador): static
    {
        if (!$this->bloqueador->contains($bloqueador)) {
            $this->bloqueador->add($bloqueador);
            $bloqueador->setBloqueador($this);
        }

        return $this;
    }

    public function removeBloqueador(Bloqueo $bloqueador): static
    {
        if ($this->bloqueador->removeElement($bloqueador)) {
            // set the owning side to null (unless already changed)
            if ($bloqueador->getBloqueador() === $this) {
                $bloqueador->setBloqueador(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bloqueo>
     */
    public function getBloqueos(): Collection
    {
        return $this->bloqueos;
    }

    public function addBloqueo(Bloqueo $bloqueo): static
    {
        if (!$this->bloqueos->contains($bloqueo)) {
            $this->bloqueos->add($bloqueo);
            $bloqueo->setBloqueado($this);
        }

        return $this;
    }

    public function removeBloqueo(Bloqueo $bloqueo): static
    {
        if ($this->bloqueos->removeElement($bloqueo)) {
            // set the owning side to null (unless already changed)
            if ($bloqueo->getBloqueado() === $this) {
                $bloqueo->setBloqueado(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitacion>
     */
    public function getInvRemitente(): Collection
    {
        return $this->Inv_remitente;
    }

    public function addInvRemitente(Invitacion $invRemitente): static
    {
        if (!$this->Inv_remitente->contains($invRemitente)) {
            $this->Inv_remitente->add($invRemitente);
            $invRemitente->setRemitente($this);
        }

        return $this;
    }

    public function removeInvRemitente(Invitacion $invRemitente): static
    {
        if ($this->Inv_remitente->removeElement($invRemitente)) {
            // set the owning side to null (unless already changed)
            if ($invRemitente->getRemitente() === $this) {
                $invRemitente->setRemitente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitacion>
     */
    public function getInvDestinatario(): Collection
    {
        return $this->Inv_destinatario;
    }

    public function addInvDestinatario(Invitacion $invDestinatario): static
    {
        if (!$this->Inv_destinatario->contains($invDestinatario)) {
            $this->Inv_destinatario->add($invDestinatario);
            $invDestinatario->setDestinatario($this);
        }

        return $this;
    }

    public function removeInvDestinatario(Invitacion $invDestinatario): static
    {
        if ($this->Inv_destinatario->removeElement($invDestinatario)) {
            // set the owning side to null (unless already changed)
            if ($invDestinatario->getDestinatario() === $this) {
                $invDestinatario->setDestinatario(null);
            }
        }

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

   
}
