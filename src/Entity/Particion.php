<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticionRepository")
 */
class Particion
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
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Memoria", inversedBy="particiones")
     * @ORM\JoinColumn(name="memoria_id", nullable=false)
     */
    private $memoria;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMemoria(): ?Memoria
    {
        return $this->memoria;
    }

    public function setMemoria(?Memoria $memoria): self
    {
        $this->memoria = $memoria;

        return $this;
    }
}
