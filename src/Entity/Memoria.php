<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemoriaRepository")
 */
class Memoria
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
     * @ORM\Column(type="integer")
     */
    private $so_size;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Particion", mappedBy="memoria", orphanRemoval=true)
     */
    private $particiones;

    public function __construct()
    {
        $this->particiones = new ArrayCollection();
    }

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

    public function getSoSize(): ?int
    {
        return $this->so_size;
    }

    public function setSoSize(int $so_size): self
    {
        $this->so_size = $so_size;

        return $this;
    }

    /**
     * @return Collection|Particion[]
     */
    public function getParticiones(): Collection
    {
        return $this->particiones;
    }

    public function addParticione(Particion $particione): self
    {
        if (!$this->particiones->contains($particione)) {
            $this->particiones[] = $particione;
            $particione->setMemoria($this);
        }

        return $this;
    }

    public function removeParticione(Particion $particione): self
    {
        if ($this->particiones->contains($particione)) {
            $this->particiones->removeElement($particione);
            // set the owning side to null (unless already changed)
            if ($particione->getMemoria() === $this) {
                $particione->setMemoria(null);
            }
        }

        return $this;
    }
}
