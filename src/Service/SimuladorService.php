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

    /*
     * Esta es la función principal, la cual se llama desde el controlador
     * y gestiona toda la simulación, pasando las distintas variables
     * por diversas funciones.
     * */
    function simular(Simulador $simulador)
    {
        $memoria = $simulador->getMemoria();
        $algoritmoIntercambio = $simulador->getAlgoritmoIntercambio();
        $procesos = $simulador->getProcesos();
        $algoritmoPlanificacion = $simulador->getAlgoritmoPlanificacion();
        $tablaEstadisticasProcesos = $this->getTablaProcesosSerializados($procesos);
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
            $ultimaRafaga = end($rafagas);
            //Se liberan de memoria los procesos que se bloquearon o finalizaron en la ultima rafaga
            $particiones = $this->liberarProcesosFinalizadosBloqueados($ultimaRafaga, $particiones);
            //Cargo la cola de nuevos con los procesos que arriban en la unidad de tiempo actual
            $cola_nuevos = $this->guardarProcesosColaNuevos($cola_nuevos, $procesos, $t, $simulador->getQuantum());

            //Se guardan los nuevos procesos a memoria
            list($cola_listos, $cola_nuevos, $particiones) =
                $this->intercambioService
                    ->asignacionMemoria(
                        $cola_listos,
                        $cola_nuevos,
                        $particiones,
                        $algoritmoIntercambio,
                        $memoria->getTipo()
                    );

            //Seteamos la rafaga inicial
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
                'ejecuto_es' => null,
                'finalizo' => null,
                'finalizo_es' => null,
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
            list($cola_bloqueados, $cola_nuevos, $rafagaActual) =
                $this->planificacionService->tratarBloqueados($cola_bloqueados, $cola_nuevos, $rafagaActual);
            //Se ejecuta el algoritmo de planificación correspondiente
            switch ($algoritmoPlanificacion) {
                case 'fcfs':
                    list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                        $this->planificacionService
                            ->fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo(), $tablaEstadisticasProcesos);
                    break;
                case 'rr':
                    list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                        $this->planificacionService
                            ->rr($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $simulador->getQuantum(), $memoria->getTipo(), $tablaEstadisticasProcesos);
                    break;
                case 'sjf':
                    list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                        $this->planificacionService
                            ->sjf($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo(), $tablaEstadisticasProcesos);
                    break;
                case 'srtf':
                    list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                        $this->planificacionService
                            ->srtf($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo(), $tablaEstadisticasProcesos);
                    break;
                case 'multinivel':
                    dd('no hay multinivel aun');
                    break;
            }

            if ($rafagaActual['finalizo']) {
                foreach ($tablaEstadisticasProcesos as $key => $procesoEstadistica) {
                    if ($procesoEstadistica['id'] == $rafagaActual['finalizo']['id']) {
                        $tablaEstadisticasProcesos[$key]['tr'] = $t+1;
                        $tablaEstadisticasProcesos[$key]['finalizo'] = true;
                    }
                }
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

        return [$rafagaInicial, $rafagas, $tablaEstadisticasProcesos];
    }

    function liberarProcesosFinalizadosBloqueados($rafaga, $particiones)
    {
        if ($rafaga) {
            if ($rafaga['finalizo']) {
                $procesoEnTratamiento = $rafaga['finalizo'];
                $particiones = $this->intercambioService
                    ->liberarProcesoDeMemoria($procesoEnTratamiento, $particiones); //Libero la memoria
            }
            if ($rafaga['bloqueo']) {
                $procesoEnTratamiento = $rafaga['bloqueo'];
                $particiones = $this->intercambioService
                    ->liberarProcesoDeMemoria($procesoEnTratamiento, $particiones); //Libero la memoria
            }
        }
        return $particiones;
    }

    function getTablaProcesosSerializados($procesos) {
        $tablaProcesos = [];
        foreach ($procesos as $key=> $proceso) {
            $procesoSerializado = [
                'id' => $key,
                'te' => 0,
                'tr' => 0,
                'uso_cpu' => 0,
                'finalizo' => false
            ];
            array_push($tablaProcesos, $procesoSerializado);
        }
        return $tablaProcesos;
    }
    /*
     * Esta función devuelve particiones serializadas en array,
     * pasando como parámetro un objeto Memoria.
     * */
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

    /*
     * Esta función serializa una partición en formato array
     * */
    function serializarParticion(Particion $particion, $id)
    {
        return [
            'id' => $id,
            'size' => $particion->getSize(),
            'color' => $particion->getColor(),
            'proceso_asignado' => null
        ];
    }

    /*
     * Esta función serializa un proceso en formato array
     * */
    function serializarProceso(Proceso $proceso, $id, $quantum = null, $colas = null)
    {
        $procesoSerializado = [
            'id' => $id,
            'size' => $proceso->getSize(),
            'ta' => $proceso->getTa(),
            'ciclo' => [
                0 => ['tipo' => 'irrupcion', 'valor' => $proceso->getTi1()],
                1 => ['tipo' => 'bloqueo', 'valor' => $proceso->getBloqueo()],
                2 => ['tipo' => 'irrupcion', 'valor' => $proceso->getTi2()]
            ],
            'uso_cpu' => 0,
            'te' => 0,
            'tr' => null,
            'ejecutandose' => 0
        ];
        if ($proceso->getPrioridad() > 0 && $proceso->getPrioridad() <= 5 ) {
            $procesoSerializado['cola'] = $colas['cola_alta'];
            if ($colas['cola_alta']['algoritmo_planificacion'] == 'rr') {
                $procesoSerializado['quantum'] = $colas['cola_alta']['quantum'];
            }
        } elseif ($proceso->getPrioridad() > 5 && $proceso->getPrioridad() <= 15) {
            $procesoSerializado['cola'] = $colas['cola_media'];
            if ($colas['cola_media']['algoritmo_planificacion'] == 'rr') {
                $procesoSerializado['quantum'] = $colas['cola_media']['quantum'];
            }
        } elseif ($proceso->getPrioridad() > 15 && $proceso->getPrioridad() <= 32) {
            $procesoSerializado['cola'] = $colas['cola_baja'];
            if ($colas['cola_baja']['algoritmo_planificacion'] == 'rr') {
                $procesoSerializado['quantum'] = $colas['cola_baja']['quantum'];
            }
        }
        if ($quantum > 0) {
            $procesoSerializado['quantum'] = $quantum;
        }
        return $procesoSerializado;
    }

    /*
     * Esta función se encarga de actualizar la cola de nuevos
     * con procesos que quieran arribar
     * */
    function guardarProcesosColaNuevos($cola_nuevos, $procesos, $rafaga, $quantum = null, $colas = null)
    {
        foreach ($procesos as $key => $proceso) {
            //Le doy formato de array al proceso
            $procesoFormateado = $this->serializarProceso($proceso, $key, $quantum, $colas);
            if ($proceso->getTa() == $rafaga) {
                array_push($cola_nuevos, $procesoFormateado);
            }
        }

        return $cola_nuevos;
    }

    /*
     * Esta función chequea si todavía queda algún proceso a tratar
     * para poder finalizar (o no) la simulación.
     * */
    function finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos, $procesos, $t)
    {
        foreach ($procesos as $proceso) {
            if ($proceso->getTa() > $t) {
                return false;
            }
        }
        return !(count($cola_listos) || count($cola_bloqueados) || count($cola_nuevos));
    }

    /*
     * Esta es la función principal, la cual se llama desde el controlador
     * y gestiona toda la simulación, pasando las distintas variables
     * por diversas funciones.
     * */
    function simularMultinivel(Simulador $simulador)
    {
        $memoria = $simulador->getMemoria();
        $algoritmoIntercambio = $simulador->getAlgoritmoIntercambio();
        $procesos = $simulador->getProcesos();
        $algoritmoPlanificacion = $simulador->getAlgoritmoPlanificacion();
        $tablaEstadisticasProcesos = $this->getTablaProcesosSerializados($procesos);

        $rafagaInicial = [];
        $rafagas = []; //Array principal donde se van a almacenar todas las ráfagas
        $cola_nuevos = []; //Esta cola resguarda todos los procesos que piden cpu
        $cola_listos = []; //Esta cola guarda los procesos que entran en memoria
        $cola_bloqueados = [];
        $colas_multinivel = $simulador->getColas();

        $cola_alta_prioridad = [];
        $cola_media_prioridad = [];
        $cola_baja_prioridad = [];

        foreach ($colas_multinivel as $cola) {
          if ($cola->getPrioridad() == 3) {
              $cola_alta_prioridad = [
                  'prioridad' => 3,
                  'algoritmo_planificacion' => $cola->getAlgoritmoPlanificacion(),
                  'quantum' => $cola->getQuantum()
              ];
          } elseif ($cola->getPrioridad() == 2) {
              $cola_media_prioridad = [
                  'prioridad' => 2,
                  'algoritmo_planificacion' => $cola->getAlgoritmoPlanificacion(),
                  'quantum' => $cola->getQuantum()
              ];
          } else {
              $cola_baja_prioridad = [
                  'prioridad' => 1,
                  'algoritmo_planificacion' => $cola->getAlgoritmoPlanificacion(),
                  'quantum' => $cola->getQuantum()
              ];
          }
        };

        $colas_multinivel = [
            'cola_alta' => $cola_alta_prioridad,
            'cola_media' => $cola_media_prioridad,
            'cola_baja' => $cola_baja_prioridad
        ];

        //Cargar particiones de la memoria dentro de un array de particiones
        $particiones = $this->getParticionesArray($memoria);

        $condicionFin = false; //Condición de fin

        //Inicializo las ráfagas
        $t = 0;

        while (!$condicionFin) {
            $ultimaRafaga = end($rafagas);
            //Se liberan de memoria los procesos que se bloquearon o finalizaron en la ultima rafaga
            $particiones = $this->liberarProcesosFinalizadosBloqueados($ultimaRafaga, $particiones);
            //Cargo la cola de nuevos con los procesos que arriban en la unidad de tiempo actual
            $cola_nuevos = $this->guardarProcesosColaNuevos($cola_nuevos, $procesos, $t, null, $colas_multinivel);

            //Se guardan los nuevos procesos a memoria
            list($cola_listos, $cola_nuevos, $particiones) =
                $this->intercambioService
                    ->asignacionMemoria(
                        $cola_listos,
                        $cola_nuevos,
                        $particiones,
                        $algoritmoIntercambio,
                        $memoria->getTipo()
                    );

//            if ($t == 0) {
//                dd($cola_listos);
//            }
            usort($cola_listos, function ($a, $b) {
                if ($a['cola']['prioridad'] == $b['cola']['prioridad']) {
                    if ($a['ejecutandose'] < $b['ejecutandose']) return 1;
                }
                return ($a['cola']['prioridad'] > $b['cola']['prioridad']) ? -1 : 1;
            });

            //Seteamos la rafaga inicial
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
                'ejecuto_es' => null,
                'finalizo' => null,
                'finalizo_es' => null,
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
            list($cola_bloqueados, $cola_nuevos, $rafagaActual) =
                $this->planificacionService->tratarBloqueados($cola_bloqueados, $cola_nuevos, $rafagaActual);

            usort($cola_listos, function ($a, $b) {
                return ($a['cola']['prioridad'] < $b['cola']['prioridad']) ? -1 : 1;
            });

            if (isset($cola_listos[0])) {
                switch ($cola_listos[0]['cola']['algoritmo_planificacion']) {
                    case 'fcfs':
                        list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                            $this->planificacionService
                                ->fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo(), $tablaEstadisticasProcesos);
                        break;
                    case 'rr':
                        list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                            $this->planificacionService
                                ->rr($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $cola_listos[0]['quantum'], $memoria->getTipo(), $tablaEstadisticasProcesos);
                        break;
                    case 'sjf':
                        list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                            $this->planificacionService
                                ->sjf($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo(), $tablaEstadisticasProcesos);
                        break;
                    case 'srtf':
                        list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tablaEstadisticasProcesos) =
                            $this->planificacionService
                                ->srtf($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $memoria->getTipo(), $tablaEstadisticasProcesos);
                        break;
                }
            }

            if ($rafagaActual['finalizo']) {
                foreach ($tablaEstadisticasProcesos as $key => $procesoEstadistica) {
                    if ($procesoEstadistica['id'] == $rafagaActual['finalizo']['id']) {
                        $tablaEstadisticasProcesos[$key]['tr'] = $t+1;
                        $tablaEstadisticasProcesos[$key]['finalizo'] = true;
//                        $tablaEstadisticasProcesos[$key]['te'] =  $tablaEstadisticasProcesos[$key]['te'] -1;
                    }
                }
            }

            //Seteo el estado de las colas para la ráfaga actual
            $rafagaActual['cola_nuevos'] = $cola_nuevos;
            $rafagaActual['cola_listos'] = $cola_listos;
            $rafagaActual['cola_bloqueados'] = $cola_bloqueados;
            $rafagaActual['particiones'] = $particiones;



            //Agrego la ráfaga actual al array del total de ráfagas
            array_push($rafagas, $rafagaActual);

            if (isset($ultimaRafaga['ejecuto']['id'])) {
                if (
                    $ultimaRafaga['ejecuto']['id'] != $rafagaActual['ejecuto']['id'] &&
                    $ultimaRafaga['bloqueo'] == null &&
                    $ultimaRafaga['finalizo'] == null
                ) {
                    $ultimaRafagaKey = key($rafagas);
                    $rafagas[$ultimaRafagaKey]['interrumpe'] = $ultimaRafaga['ejecuto'];
                }
            }

            //Pregunto si la cola de listos tiene todos los procesos ya cargadados
            $condicionFin = $this->finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos, $procesos, $t);
            ++$t;
        }
        return [$rafagaInicial, $rafagas, $tablaEstadisticasProcesos];
    }
}
