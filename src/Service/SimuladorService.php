<?php

namespace App\Service;

use App\Entity\Memoria;
use App\Entity\Particion;
use App\Entity\Proceso;
use App\Entity\Simulador;

class SimuladorService
{
    private $planificacionService;
    private $intercambioService;

    public function __construct(PlanificacionService $planificacionService, IntercambioService $intercambioService)
    {
        $this->planificacionService = $planificacionService;
        $this->intercambioService = $intercambioService;
    }

    function simular(Simulador $simulador)
    {
        $memoria = $simulador->getMemoria();
        $algoritmoIntercambio = $simulador->getAlgoritmoIntercambio();
        $procesos = $simulador->getProcesos();
        $algoritmoPlanificacion = $simulador->getAlgoritmoPlanificacion();

        $rafagaInicial = [];
        $rafagas = []; //Array principal donde se van a almacenar todas las ráfagas
        $cola_nuevos = []; //Esta cola resguarda todos los procesos que piden cpu
        $cola_listos = []; //Esta cola guarda los procesos que entran en memoria
        $cola_bloqueados = [];

        //Cargar particiones de la memoria dentro de un array de particiones
        $particiones = $this->getParticionesArray($memoria);

        $condicionFin = false; //Condición de fin

        //Inicializo las ráfagas
        $t = 0;

        while (!$condicionFin) {

            //Cargo la cola de nuevos con los procesos que arriban en la unidad de tiempo actual
            $cola_nuevos = $this->guardarProcesosColaNuevos($cola_nuevos, $procesos, $t, $simulador->getQuantum());

            if ($memoria->getTipo() == 'fijas') {
                //Llamo a la funcion de asignación de memoria
                list($cola_listos, $cola_nuevos, $particiones) =
                    $this->intercambioService
                        ->asignacionParticionesFijas(
                            $cola_listos,
                            $cola_nuevos,
                            $particiones,
                            $algoritmoIntercambio
                        )
                ;
            } elseif ($memoria->getTipo() == 'variables') {
                list($cola_listos, $cola_nuevos, $particiones) =
                    $this->intercambioService
                        ->asignacionParticionesVariables(
                            $cola_listos,
                            $cola_nuevos,
                            $particiones,
                            $algoritmoIntercambio
                        )
                ;
            }

            if ($t == 0) {
                $rafagaInicial = [
                    'cola_nuevos' => $cola_nuevos,
                    'cola_listos' => $cola_listos,
                    'particiones' => $particiones,
                ];
            }

            //Pongo en null toda la rafaga actual para preparar la ejecución del proceso
            $rafagaActual = [
              'ejecuto' => null,
              'finalizo' => null,
              'bloqueo' => null,
              'cola_nuevos' => null,
              'cola_listos' => null,
              'cola_bloqueados' => null,
              'particiones' => null,
            ];

            /*
             * Actualizo las colas de bloqueados y nuevos
             * recorriendo los procesos que se bloquearon por una entrada/salida
             */
            list($cola_bloqueados, $cola_nuevos) =
              $this->planificacionService->tratarBloqueados($cola_bloqueados, $cola_nuevos)
            ;
            switch ($algoritmoPlanificacion) {
                case 'fcfs':
                    /*
                     * Ejecuto el algoritmo de planificación actualizando
                     * las colas, las particiones (si hay que liberar) y
                     * la rafaga actual
                     */
                    list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual) =
                        $this->planificacionService
                            ->fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo())
                    ;
                    break;
                case 'rr':
                    /*
                     * Ejecuto el algoritmo de planificación actualizando
                     * las colas, las particiones (si hay que liberar) y
                     * la rafaga actual
                     */
                    list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual) =
                      $this->planificacionService
                        ->rr($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $simulador->getQuantum(), $memoria->getTipo())
                    ;
                    break;
                case 'prioridades':
                    dd('no hay prioridades aun');
                    break;
                case 'multinivel':
                    dd('no hay multinivel aun');
                    break;
            }


            //Seteo el estado de las colas para la ráfaga actual
            $rafagaActual['cola_nuevos'] = $cola_nuevos;
            $rafagaActual['cola_listos'] = $cola_listos;
            $rafagaActual['cola_bloqueados'] = $cola_bloqueados;
            $rafagaActual['particiones'] = $particiones;

            //Agrego la ráfaga actual al array del total de ráfagas
            array_push($rafagas, $rafagaActual);

            //Pregunto si la cola de listos tiene todos los procesos ya cargadados
            $condicionFin = $this->finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos, $procesos, $t);
            ++$t;
        }
        return [$rafagaInicial, $rafagas];
    }

    function getParticionesArray(Memoria $memoria)
    {
        $particiones = [];
        foreach ($memoria->getParticiones() as $key => $particion) {
            //Le doy formato de array a cada partición
            $particionSerializada = $this->serializarParticion($particion, $key);

            //Agrego al array de particiones
            array_push($particiones, $particionSerializada);
        }

        return $particiones;

    }

    function serializarParticion(Particion $particion, $id)
    {
        return [
          'id' => $id,
          'size' => $particion->getSize(),
          'proceso_asignado' => null
        ];
    }

    function serializarProceso(Proceso $proceso, $id, $quantum = null)
    {
        $procesoSerializado = [
          'id' => $id,
          'size' => $proceso->getSize(),
          'ta' => $proceso->getTa(),
          'ciclo' => [
            0 => ['tipo' => 'irrupcion', 'valor' => $proceso->getTi1()],
            1 => ['tipo' => 'bloqueo', 'valor' => $proceso->getBloqueo()],
            2 => ['tipo' => 'irrupcion', 'valor' => $proceso->getTi2()]
          ]
        ];
        if ($quantum > 0) {
            $procesoSerializado['quantum'] = $quantum;
        }
        return $procesoSerializado;
    }

    function guardarProcesosColaNuevos($cola_nuevos, $procesos, $rafaga, $quantum = null)
    {
        foreach ($procesos as $key => $proceso) {
            //Le doy formato de array al proceso
            $procesoFormateado = $this->serializarProceso($proceso, $key, $quantum);
            if ($proceso->getTa() == $rafaga) {
                array_push($cola_nuevos, $procesoFormateado);
            }
        }

        return $cola_nuevos;
    }

    function finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos, $procesos, $t) {
        foreach ($procesos as $proceso) {
            if ($proceso->getTa() > $t) {
                return false;
            }
        }
        return !(count($cola_listos) || count($cola_bloqueados) || count($cola_nuevos));
    }
}
