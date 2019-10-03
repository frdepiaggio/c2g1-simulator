<?php

namespace App\Service;

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

        $condicionFin = false; //Condición de fin

        //Inicializo las ráfagas
        $t = 0;

        while (!$condicionFin) {

            //Cargo la cola de nuevos con los procesos que arriban en la unidad de tiempo actual
            $cola_nuevos = $this->guardarProcesosColaNuevos($cola_nuevos, $procesos, $t);

            //Llamo a la funcion de asignación de memoria
            list($cola_listos, $cola_nuevos, $particiones) =
              $this->asignacionParticionesFijasFF($cola_listos, $cola_nuevos, $particiones);

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
            $condicionFin = $this->finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos);
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
          ]
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
                  $proceso['size'] <= $particion['size']
                ) {
                    //Asigno el proceso a la partición
                    $particiones[$particionKey]['proceso_asignado'] = $cola_nuevos[$procesoKey];
                    //Pongo el proceso en la cola de listos
                    array_push($cola_listos, $cola_nuevos[$procesoKey]);
                    //Saco el proceso de la cola de nuevos
                    unset($cola_nuevos[$procesoKey]);
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

    function fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual) {
        if (!empty($cola_listos)) {
            $procesoEnTratamiento = $cola_listos[0];
            $ciclo = $procesoEnTratamiento['ciclo'];

            if ($ciclo[0]['tipo'] == 'irrupcion') {
                $tiempo_remanente = $ciclo[0]['valor'] - 1;

                if ($tiempo_remanente == 0 && isset($ciclo[1])) { //Si se termina la irrupcion y viene un bloqueo
                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino
                    $particiones = $this->liberarProcesoDeMemoria($procesoEnTratamiento, $particiones); //Libero la memoria
                    array_push($cola_bloqueados, $procesoEnTratamiento);

                    $rafagaActual['bloqueo'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                } else if ($tiempo_remanente == 0 && !isset($ciclo[1])) { // Si termina la irrupción y termina el proceso
                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el ciclo del proceso
                    $particiones = $this->liberarProcesoDeMemoria($procesoEnTratamiento, $particiones); //Libero la memoria
                    $rafagaActual['finalizo'] = $procesoEnTratamiento; //Cargar proceso finalizado

                } else {
                    //El proceso se ejecuta normalmente y sigue en CPU
                    $ciclo[0]['valor'] = $tiempo_remanente; //Se resta la irrupcion
                    $cola_listos[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de listos
                    $rafagaActual['ejecuto'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                }
            }
        }

        return [array_values($cola_listos), array_values($cola_bloqueados), $particiones, $rafagaActual];
    }


    function tratarBloqueados($cola_bloqueados, $cola_nuevos) {
        if (!empty($cola_bloqueados)) {
            $procesoEnTratamiento = $cola_bloqueados[0];
            $ciclo = $cola_bloqueados[0]['ciclo'];

            if ($ciclo[0]['tipo'] == 'bloqueo') {
                $bloqueo_remanente = $ciclo[0]['valor'] - 1;

                if ($bloqueo_remanente == 0 ) { //Si se termina la irrupcion y viene un bloqueo
                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_bloqueados[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino
                    array_push($cola_nuevos, $procesoEnTratamiento); //El proceso vuelve a la cola de nuevos a competir por memoria

                } else {
                    //El proceso se ejecuta normalmente y sigue en E/S
                    $ciclo[0]['valor'] = $bloqueo_remanente; //Se resta la irrupcion
                    $cola_bloqueados[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de bloqueados

                }
            }
        }

        return [array_values($cola_bloqueados), array_values($cola_nuevos)];
    }

    function finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos) {

        return !(count($cola_listos) || count($cola_bloqueados) || count($cola_nuevos));
    }
}
