<?php

namespace App\Entity;

use App\Repository\ChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatRepository::class)]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(length: 20)]
    private ?string $tipo = null;

    #[ORM\ManyToOne(inversedBy: 'chats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $creador = null;

    /**
     * @var Collection<int, Usuario>
     */
    #[ORM\ManyToMany(targetEntity: Usuario::class, inversedBy: 'chats')]
    private Collection $participantes;

    /**
     * @var Collection<int, Invitacion>
     */
    #[ORM\OneToMany(targetEntity: Invitacion::class, mappedBy: 'chat')]
    private Collection $Inv_chat;

    public function __construct()
    {
        $this->participantes = new ArrayCollection();
        $this->Inv_chat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getCreador(): ?Usuario
    {
        return $this->creador;
    }

    public function setCreador(?Usuario $creador): static
    {
        $this->creador = $creador;

        return $this;
    }

    /**
     * @return Collection<int, Usuario>
     */
    public function getParticipantes(): Collection
    {
        return $this->participantes;
    }

    public function addParticipante(Usuario $participante): static
    {
        if (!$this->participantes->contains($participante)) {
            $this->participantes->add($participante);
        }

        return $this;
    }

    public function removeParticipante(Usuario $participante): static
    {
        $this->participantes->removeElement($participante);

        return $this;
    }

    /**
     * @return Collection<int, Invitacion>
     */
    public function getInvChat(): Collection
    {
        return $this->Inv_chat;
    }

    public function addInvChat(Invitacion $invChat): static
    {
        if (!$this->Inv_chat->contains($invChat)) {
            $this->Inv_chat->add($invChat);
            $invChat->setChat($this);
        }

        return $this;
    }

    public function removeInvChat(Invitacion $invChat): static
    {
        if ($this->Inv_chat->removeElement($invChat)) {
            // set the owning side to null (unless already changed)
            if ($invChat->getChat() === $this) {
                $invChat->setChat(null);
            }
        }

        return $this;
    }
}
