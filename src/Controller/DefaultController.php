<?php

namespace App\Controller;

use App\Entity\Memoria;
use App\Entity\Particion;
use App\Entity\Proceso;
use App\Entity\Simulador;
use App\Service\SimuladorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [

        ]);
    }
    /**
     * @Route("/nuevo", name="new")
     */
    public function new()
    {
        return $this->render('simulador/new.html.twig', [

        ]);
    }

    /**
     * @Route("/simular/{id}", name="simular")
     * @param Simulador $simulador
     * @param SimuladorService $simuladorService
     * @return Response
     */
    public function simular(Simulador $simulador, SimuladorService $simuladorService)
    {
        $procesos = $simulador->getProcesos();
        $memoria = $simulador->getMemoria();
        list($rafagaInicial, $rafagas) = $simuladorService->simular($simulador);

        return $this->render('simulador/output.html.twig', [
            'controller_name' => 'DefaultController',
            'rafagaInicial' => $rafagaInicial,
            'rafagas' => $rafagas,
            'memoria' => $memoria,
            'procesos' => $procesos,
        ]);
    }
}
