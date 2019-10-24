<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SimuladorRepository")
 */
class Simulador
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $algoritmoPlanificacion;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $algoritmoIntercambio;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantum;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Memoria", inversedBy="simulador", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $memoria;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Proceso", mappedBy="simulador")
     */
    private $procesos;

    public function __construct()
    {
        $this->procesos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlgoritmoPlanificacion(): ?string
    {
        return $this->algoritmoPlanificacion;
    }

    public function setAlgoritmoPlanificacion(?string $algoritmoPlanificacion): self
    {
        $this->algoritmoPlanificacion = $algoritmoPlanificacion;

        return $this;
    }

    public function getAlgoritmoIntercambio(): ?string
    {
        return $this->algoritmoIntercambio;
    }

    public function setAlgoritmoIntercambio(?string $algoritmoIntercambio): self
    {
        $this->algoritmoIntercambio = $algoritmoIntercambio;

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

    public function getMemoria(): ?Memoria
    {
        return $this->memoria;
    }

    public function setMemoria(Memoria $memoria): self
    {
        $this->memoria = $memoria;

        return $this;
    }

    /**
     * @return Collection|Proceso[]
     */
    public function getProcesos(): Collection
    {
        return $this->procesos;
    }

    public function addProceso(Proceso $proceso): self
    {
        if (!$this->procesos->contains($proceso)) {
            $this->procesos[] = $proceso;
            $proceso->setSimulador($this);
        }

        return $this;
    }

    public function removeProceso(Proceso $proceso): self
    {
        if ($this->procesos->contains($proceso)) {
            $this->procesos->removeElement($proceso);
            // set the owning side to null (unless already changed)
            if ($proceso->getSimulador() === $this) {
                $proceso->setSimulador(null);
            }
        }

        return $this;
    }
}
