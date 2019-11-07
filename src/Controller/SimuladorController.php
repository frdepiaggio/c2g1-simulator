<?php

namespace App\Controller;

use App\Entity\Memoria;
use App\Entity\Particion;
use App\Entity\Proceso;
use App\Entity\Simulador;
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
     * @Route("/", name="simulador_index", methods={"GET"})
     */
    public function index(): Response
    {
        $simuladores = $this->getDoctrine()
          ->getRepository(Simulador::class)
          ->findAll();

        return $this->render('simulador/index.html.twig', ['simuladores' => $simuladores]);
    }

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
                $memoria->setTipo($memoriaJson['tipo']);

                $em->persist($memoria);
                $em->flush();

                $partQty = 0;
                $maxParticion = 0;
                foreach ($memoriaJson['particiones'] as $particionArray) {
                    $particion = new Particion();
                    $particion->setSize($particionArray['size']);
                    $particion->setColor($particionArray['color']);
                    $particion->setMemoria($memoria);
                    $em->persist($particion);
                    if ($particion->getSize() > $maxParticion) {
                        $maxParticion = $particion->getSize();
                    }
                    ++$partQty;
                }

                $simulador = new Simulador();
                $simulador->setMemoria($memoria);
                $simulador->setAlgoritmoIntercambio($simuladorJson['algoritmo_intercambio']);

                $em->persist($simulador);
                $em->flush();
                $em->refresh($memoria);

                $response['mensaje'] = 'Se cargo el simulador '.$simulador->getId().
                  'la memoria ' .$memoria->getId().' con '.$partQty .' particiones';
                $response['newMemoriaId'] = $memoria->getId();
                $response['newSimuladorId'] = $simulador->getId();
                $response['maximaParticionSize'] = $maxParticion;
            } catch (\Exception $e) {
                $response['code'] = 500;
                $response['mensaje'] = 'Error: '. $e->getMessage();
            }
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/new-proceso", name="simulador.new-proceso", methods={"GET","POST"})
     * @param Request $request
     * @param NewSimuladorService $newSimuladorService
     * @return JsonResponse
     */
    public function newProceso(Request $request, NewSimuladorService $newSimuladorService) :Response
    {
        $em = $this->getDoctrine()->getManager();
        $procesoJson = $request->get('proceso');
        $response = $newSimuladorService->validarFormProceso($procesoJson);

        $qb = $em->getRepository('App:Simulador');
        $simulador = $qb->findOneBy(array('id'=>$procesoJson['id_simulador']));

        if ($response['code'] == 200) {
            try {
                $proceso = new Proceso();
                $proceso->setSize(intval($procesoJson['size']));
                $proceso->setTa(intval($procesoJson['ta']));
                $proceso->setTi1(intval($procesoJson['ti1']));
                $proceso->setTi2(intval($procesoJson['ti2']));
                $proceso->setBloqueo(intval($procesoJson['es']));
                $proceso->setStatus('creado');
                $proceso->setSimulador($simulador);
                $em->persist($proceso);
                $em->flush();

                $response['mensaje'] = 'Se cargo el proceso '.$proceso->getId();
                $response['newProcesoId'] = $proceso->getId();
            } catch (\Exception $e) {
                $response['code'] = 500;
                $response['mensaje'] = 'Error: '. $e->getMessage();
            }
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/new-simulador", name="simulador.new-simulador", methods={"GET","POST"})
     * @param Request $request
     * @param NewSimuladorService $newSimuladorService
     * @return JsonResponse
     */
    public function newSimulador(Request $request, NewSimuladorService $newSimuladorService) :Response
    {
        $em = $this->getDoctrine()->getManager();
        $simuladorJson = $request->get('simulador');
        $response = $newSimuladorService->validarFormSimulador($simuladorJson);

        $qb = $em->getRepository('App:Simulador');
        $simulador = $qb->findOneBy(array('id'=>$simuladorJson['id']));
        if ($response['code'] == 200) {
            try {
                $algoritmoPlanificacion = $simuladorJson['algoritmo_planificacion'];
                $quantum = intval($simuladorJson['quantum']);
                $simulador->setAlgoritmoPlanificacion($algoritmoPlanificacion);
                $simulador->setQuantum($quantum);
                $em->flush();

                $response['mensaje'] = 'Se cargo el simulador '.$simulador->getId();
                $response['simulador'] = $simulador->getId();
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
