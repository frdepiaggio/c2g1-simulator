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
        $memoria = $simulador->getMemoria();

//        if ($simulador->getRafagas() && $simulador->getRafagaInicial()) {
//            $rafagas = $simulador->getRafagas();
//            $rafagaInicial = $simulador->getRafagaInicial();
//        } else {
            if ($simulador->getAlgoritmoPlanificacion() == 'multinivel') {
                list($rafagaInicial, $rafagas, $estadisticas) = $simuladorService->simularMultinivel($simulador);
            } else {
                list($rafagaInicial, $rafagas, $estadisticas) = $simuladorService->simular($simulador);
            }
//
            $rafagaFinal = [
                'ejecuto' => null,
                'ejecuto_es' => null,
                'finalizo' => null,
                'finalizo_es' => null,
                'bloqueo' => null,
                'cola_nuevos' => null,
                'cola_listos' => null,
                'cola_bloqueados' => null,
                'particiones' => $simuladorService->getParticionesArray($memoria),
                'fragmentacion_externa' => null
            ];
            array_push($rafagas, $rafagaFinal);
//
//            $em = $this->getDoctrine()->getManager();
//            $simulador->setRafagas($rafagas);
//            $simulador->setRafagaInicial($rafagaInicial);
//
//            $em->flush();
//        }
        $fragmentacion = 0;
        if ($memoria->getTipo() == 'fijas') {
            foreach ($rafagas as $rafaga) {
                foreach ($rafaga['particiones'] as $particion) {
                    if (!is_null($particion['fragmentacion_interna'])) {
                        $fragmentacion = $fragmentacion + $particion['fragmentacion_interna'];
                    }
                }
            }
        } else {
            foreach ($rafagas as $rafaga) {
                $fragmentacion = $fragmentacion + $rafaga['fragmentacion_externa'];
            }

        }

        return $this->render('simulador/output.html.twig', [
            'rafagaInicial' => $rafagaInicial,
            'rafagas' => $rafagas,
            'simulador' => $simulador,
            'estadisticas' => $estadisticas,
            'fragmentacion' => $fragmentacion
        ]);
    }
}
