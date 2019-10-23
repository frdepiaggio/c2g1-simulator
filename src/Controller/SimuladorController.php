<?php

namespace App\Controller;

use App\Entity\Memoria;
use App\Entity\Particion;
use App\Entity\Proceso;
use App\Service\SimuladorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\NewSimuladorService;

/**
 * @Route("/simulador")
 */

class SimuladorController extends AbstractController
{
    /**
     * @Route("/new-memoria", name="simulador.new-memoria", methods={"GET","POST"})
     * @param Request $request
     * @param NewSimuladorService $newSimuladorService
     * @return JsonResponse
     */
    public function newMemoria(Request $request, NewSimuladorService $newSimuladorService) :Response
    {
        $em = $this->getDoctrine()->getManager();
        $memoriaJson = $request->get('memoria');
        $simuladorJson = $request->get('simulador');
        $response = $newSimuladorService->validarFormMemoria($memoriaJson);

        if ($response['code'] == 200) {
            try {
                $memoria = new Memoria();
                $memoria->setSize(intval($memoriaJson['totalSize']));
                $memoria->setSoSize(intval($memoriaJson['soSize']));
                $em->persist($memoria);
                $em->flush();

                $partQty = 0;
                foreach ($memoriaJson['particiones'] as $particionSize) {
                    $particion = new Particion();
                    $particion->setSize($particionSize);
                    $particion->setMemoria($memoria);
                    $em->persist($particion);
                    ++$partQty;
                }
                $em->flush();
                $em->refresh($memoria);

                $response['mensaje'] = 'Se cargo la memoria ' . $memoria->getId() .' con '.$partQty .' particiones';
                $response['newMemoriaId'] = $memoria->getId();
            } catch (\Exception $e) {
                $response['code'] = 500;
                $response['mensaje'] = 'Error: '. $e->getMessage();
            }
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/prueba/{id}", name="simulador.prueba", methods={"GET","POST"})
     * @param Memoria $memoria
     * @return void
     */
    function prueba(Memoria $memoria) {
        dd(count($memoria->getParticiones()));

    }
}
