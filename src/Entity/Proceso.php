<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProcesoRepository")
 */
class Proceso
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $ta;

    /**
     * @ORM\Column(type="integer")
     */
    private $ti1;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $bloqueo;

    /**
     * @ORM\Column(type="integer")
     */
    private $ti2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Simulador", inversedBy="procesos")
     * @ORM\JoinColumn(name="simulador", referencedColumnName="id", onDelete="CASCADE")
     */
    private $simulador;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTa(): ?int
    {
        return $this->ta;
    }

    public function setTa(int $ta): self
    {
        $this->ta = $ta;

        return $this;
    }

    public function getTi1(): ?int
    {
        return $this->ti1;
    }

    public function setTi1(int $ti1): self
    {
        $this->ti1 = $ti1;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBloqueo(): ?int
    {
        return $this->bloqueo;
    }

    public function setBloqueo(int $bloqueo): self
    {
        $this->bloqueo = $bloqueo;

        return $this;
    }

    public function getTi2(): ?int
    {
        return $this->ti2;
    }

    public function setTi2(int $ti2): self
    {
        $this->ti2 = $ti2;

        return $this;
    }

    public function getSimulador(): ?Simulador
    {
        return $this->simulador;
    }

    public function setSimulador(?Simulador $simulador): self
    {
        $this->simulador = $simulador;

        return $this;
    }
}
