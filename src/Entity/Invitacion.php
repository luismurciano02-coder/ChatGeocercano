<?php

namespace App\Entity;

use App\Repository\InvitacionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvitacionRepository::class)]
class Invitacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'Inv_remitente')]
    private ?Usuario $remitente = null;

    #[ORM\ManyToOne(inversedBy: 'Inv_destinatario')]
    private ?Usuario $destinatario = null;

    #[ORM\ManyToOne(inversedBy: 'Inv_chat')]
    private ?Chat $chat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemitente(): ?Usuario
    {
        return $this->remitente;
    }

    public function setRemitente(?Usuario $remitente): static
    {
        $this->remitente = $remitente;

        return $this;
    }

    public function getDestinatario(): ?Usuario
    {
        return $this->destinatario;
    }

    public function setDestinatario(?Usuario $destinatario): static
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): static
    {
        $this->chat = $chat;

        return $this;
    }
}
