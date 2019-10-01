<?php

namespace App\Controller;

use App\Entity\Memoria;
use App\Entity\Particion;
use App\Entity\Proceso;
use App\Service\SimuladorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(SimuladorService $simuladorService)
    {
        $memoria = new Memoria();
        $memoria->setSize(128)->setSoSize(16);

        $particion1 = new Particion();
        $particion1->setSize(64)->setMemoria($memoria);
        $memoria->addParticione($particion1);

        $particion2 = new Particion();
        $particion2->setSize(32)->setMemoria($memoria);
        $memoria->addParticione($particion2);

        $particion3 = new Particion();
        $particion3->setSize(16)->setMemoria($memoria);
        $memoria->addParticione($particion3);

        $proceso1 = new Proceso();
        $proceso1->setTa(0)->setSize(30)->setTi(2)->setStatus('creado');

        $proceso2 = new Proceso();
        $proceso2->setTa(0)->setSize(12)->setTi(1)->setStatus('creado');

        $proceso3 = new Proceso();
        $proceso3->setTa(0)->setSize(58)->setTi(3)->setStatus('creado');

        $proceso4 = new Proceso();
        $proceso4->setTa(1)->setSize(5)->setTi(5)->setStatus('creado');

        $proceso5 = new Proceso();
        $proceso5->setTa(1)->setSize(16)->setTi(1)->setStatus('creado');

        $proceso6 = new Proceso();
        $proceso6->setTa(2)->setSize(32)->setTi(2)->setStatus('creado');

        $procesos = [$proceso1, $proceso2, $proceso3, $proceso4, $proceso5, $proceso6];

        $rafagas = $simuladorService->simular($memoria, $procesos);

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'rafagas' => $rafagas,
            'memoria' => $memoria,
            'procesos' => $procesos,
        ]);
    }
}
