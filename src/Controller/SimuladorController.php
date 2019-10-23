<?php

namespace App\Controller;

use App\Entity\Memoria;
use App\Entity\Particion;
use App\Entity\Proceso;
use App\Service\SimuladorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/simulador")
 */

class SimuladorController extends AbstractController
{
    /**
     * @Route("/new-memoria", name="simulador.new-memoria")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [

        ]);
    }
}
