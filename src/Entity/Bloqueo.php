<?php

namespace App\Entity;

use App\Repository\BloqueoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BloqueoRepository::class)]
class Bloqueo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bloqueador')]
    private ?Usuario $bloqueador = null;

    #[ORM\ManyToOne(inversedBy: 'bloqueos')]
    private ?Usuario $bloqueado = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBloqueador(): ?Usuario
    {
        return $this->bloqueador;
    }

    public function setBloqueador(?Usuario $bloqueador): static
    {
        $this->bloqueador = $bloqueador;

        return $this;
    }

    public function getBloqueado(): ?Usuario
    {
        return $this->bloqueado;
    }

    public function setBloqueado(?Usuario $bloqueado): static
    {
        $this->bloqueado = $bloqueado;

        return $this;
    }
}
