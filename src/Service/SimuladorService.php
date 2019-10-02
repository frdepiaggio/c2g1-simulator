<?php

namespace App\Service;

use App\Entity\Proceso;


class SimuladorService
{
    function simular($memoria, $procesos)
    {
        $rafagas = []; //Array principal donde se van a almacenar todas las ráfagas
        $cola_nuevos = []; //Esta cola resguarda todos los procesos que piden cpu
        $cola_listos = []; //Esta cola guarda los procesos que entran en memoria
        $cola_bloqueados = [];

        //Cargar particiones de la memoria dentro de un array de particiones
        $particiones = $this->getParticionesArray($memoria);

        $condicionFin = true; //Condición de fin

        //Inicializo las ráfagas
        $t = 0;

        while ($condicionFin) {

            //Cargo la cola de nuevos con los procesos que arriban en la unidad de tiempo actual
            $cola_nuevos = $this->guardarProcesosColaNuevos($cola_nuevos, $procesos, $t);

            //Llamo a la funcion de asignación de memoria
            list($cola_listos, $cola_nuevos, $particiones) =
              $this->asignacionParticionesFijasFF($cola_listos, $cola_nuevos, $particiones);

            //Pongo en null toda la rafaga actual para preparar la ejecución del proceso
            $rafagaActual = [
              'ejecutandose' => null,
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
              $this->tratarBloqueados($cola_bloqueados, $cola_nuevos);

            /*
             * Ejecuto el algoritmo de planificación actualizando
             * las colas, las particiones (si hay que liberar) y
             * la rafaga actual
             */
            list($cola_listos, $cola_bloqueados, $particiones, $rafagaActual) =
              $this->fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual);

            //Seteo el estado de las colas para la ráfaga actual
            $rafagaActual['cola_nuevos'] = $cola_nuevos;
            $rafagaActual['cola_listos'] = $cola_listos;
            $rafagaActual['cola_bloqueados'] = $cola_bloqueados;
            $rafagaActual['particiones'] = $particiones;

            //Agrego la ráfaga actual al array del total de ráfagas
            array_push($rafagas, $rafagaActual);

            //Pregunto si la cola de listos tiene todos los procesos ya cargadados
            $condicionFin = $this->finalizoSimulador($cola_listos, $procesos);
            ++$t;
        }
        return $rafagas;
    }

    function getParticionesArray($memoria)
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

    function serializarParticion($particion, $id)
    {
        return [
          'id' => $id,
          'size' => $particion->getSize(),
          'proceso_asignado' => null
        ];
    }

    function serializarProceso($proceso, $id)
    {
        return [
          'id' => $id,
          'size' => $proceso->getSize(),
          'ta' => $proceso->getTa(),
          'ciclo' => [
            0 => ['tipo' => 'irrupcion', 'valor' => $proceso->getTi1()],
            1 => ['tipo' => 'bloqueo', 'valor' => $proceso->getBloqueo()],
            2 => ['tipo' => 'irrupcion', 'valor' => $proceso->getTi2()]
          ],
          'status' => 'nuevo'
        ];
    }

    function asignacionParticionesFijasFF($cola_listos, $cola_nuevos, $particiones)
    {
        //Recorro las particiones
        foreach ($particiones as $particionKey => $particion) {
            //Recorro los procesos
            foreach ($cola_nuevos as $procesoKey => $proceso) {
                //Asigno si el proceso cabe en la particion y si tiene de status nuevo
                if ($particiones[$particionKey]['proceso_asignado'] == null and
                  $proceso['size'] <= $particion['size'] and
                  $proceso['status'] == 'nuevo'
                ) {
                    //Asigno listo al estado del proceso en la cola de nuevos
                    $cola_nuevos[$procesoKey]['status'] = 'listo';
                    //Asigno el proceso a la partición
                    $particiones[$particionKey]['proceso_asignado'] = $cola_nuevos[$procesoKey];
                    //Pongo el proceso en la cola de listos
                    array_push($cola_listos, $cola_nuevos[$procesoKey]);
                }
            }
        }

        return [$cola_listos, $cola_nuevos, $particiones];
    }

    function guardarProcesosColaNuevos($cola_nuevos, $procesos, $rafaga)
    {
        foreach ($procesos as $key => $proceso) {
            //Le doy formato de array al proceso
            $procesoFormateado = $this->serializarProceso($proceso, $key);
            if ($proceso->getTa() == $rafaga) {
                array_push($cola_nuevos, $procesoFormateado);
            }
        }

        return $cola_nuevos;
    }

    function liberarProcesoDeMemoria($proceso, $particiones){
        foreach ($particiones as $key => $particion) {
            if ($particion['proceso_asignado']['id'] == $proceso['id'] ) {
                $particiones[$key]['proceso_asignado'] = null;
            }
        }
        return $particiones;
    }

    function finalizoSimulador($cola_listos, $procesos) {
        if (count($cola_listos) == count($procesos)) {
            //Recorro la cola de listos
            foreach ($cola_listos as $proceso) {
                if ($proceso['status'] == 'listo' or $proceso['status'] == 'bloqueado') {
                    //Si hay algún proceso listo todavía no hay que terminar
                    return true;
                }
            }
        }
        //Si no entro en el "return true" de arriba significa que todavia hay procesos para ejecutar
        return false;
    }


    function fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual) {

        foreach ( $cola_listos as $listoKey => $item) {
            if ($cola_listos[$listoKey]['status'] == 'listo') {
                //Recorro los ciclos del proceso
                foreach ($cola_listos[$listoKey]['ciclo'] as $cicloKey => $ciclo) {
                    $tipoCiclo = $ciclo['tipo'];
                    $valorCiclo = $ciclo['valor'];

                    //Si el tipo de ciclo es una irrupcion
                    if ($tipoCiclo == 'irrupcion' and $valorCiclo > 0) {
                        $tiempo_remanente = $valorCiclo -1;
                        $cola_listos[$listoKey]['ciclo'][$cicloKey] = $tiempo_remanente;
                        $rafagaActual['ejecutandose'] = $cola_listos[$listoKey];

                        //Si termino la irrupcion
                        if ($tiempo_remanente == 0) {
                            $siguienteCiclo = $cicloKey +1;
                            //Pregunto si hay un siguiente ciclo
                            if (isset($cola_listos[$listoKey]['ciclo'][$siguienteCiclo])) {
                                //Cambio el status de "listo" a "bloqueado"
                                $cola_listos[$listoKey]['status'] = 'bloqueado';
                                $rafagaActual['bloqueo'] = $cola_listos[$listoKey];
                                //Agrego el proceso a la cola de bloqueados
                                array_push($cola_bloqueados, $cola_listos[$listoKey]);
                                //Libero la memoria
                                $particiones = $this->liberarProcesoDeMemoria($cola_listos[$listoKey], $particiones);
                            } else {
                                //Si no hay otro ciclo significa que el proceso finalizó
                                $cola_listos[$listoKey]['status'] = 'finalizado';
                                $rafagaActual['finalizo'] = $cola_listos[$listoKey];

                                //Libero la memoria si finalizó algun proceso en la ráfaga actual
                                $particiones = $this->liberarProcesoDeMemoria($rafagaActual['finalizo'], $particiones);
                            }
                        }
                        //Si ya se trato la irrupción sale del while
                        return [$cola_listos, $cola_bloqueados, $particiones, $rafagaActual];
                    }
                }
            }
        }
        return [$cola_listos, $cola_bloqueados, $particiones, $rafagaActual];
    }

    function tratarBloqueados($cola_bloqueados, $cola_nuevos) {
        foreach ($cola_bloqueados as $key => $procesoBloqueado) {
            if ($procesoBloqueado['status'] == 'bloqueado') {
                foreach ($procesoBloqueado['ciclo'] as $cicloKey => $ciclo) {
                    $tipoCiclo = $ciclo['tipo'];
                    $valorCiclo = $ciclo['valor'];
                    if ($tipoCiclo == 'bloqueo' and $valorCiclo > 0) {
                        $bloqueo_remanente = $valorCiclo -1;
                        $cola_bloqueados[$key]['ciclo'][$cicloKey] = $bloqueo_remanente;
                        if ($bloqueo_remanente == 0) {
                            $cola_bloqueados[$key]['status'] = 'nuevo';
                            array_push($cola_nuevos, $cola_bloqueados[$key]);
                        }

                        return [$cola_bloqueados, $cola_nuevos];
                    }
                }
            }
        }
        return [$cola_bloqueados, $cola_nuevos];
    }
}
