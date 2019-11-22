<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ColaRepository")
 */
class Cola
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
    private $prioridad;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Simulador", inversedBy="colas")
     * @ORM\JoinColumn(name="simulador", referencedColumnName="id", onDelete="CASCADE")
     */
    private $simulador;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $algoritmoPlanificacion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantum;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrioridad(): ?int
    {
        return $this->prioridad;
    }

    public function setPrioridad(int $prioridad): self
    {
        $this->prioridad = $prioridad;

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

    public function getAlgoritmoPlanificacion(): ?string
    {
        return $this->algoritmoPlanificacion;
    }

    public function setAlgoritmoPlanificacion(string $algoritmoPlanificacion): self
    {
        $this->algoritmoPlanificacion = $algoritmoPlanificacion;

        return $this;
    }

    public function getQuantum(): ?int
    {
        return $this->quantum;
    }

    public function setQuantum(?int $quantum): self
    {
        $this->quantum = $quantum;

        return $this;
    }
}
