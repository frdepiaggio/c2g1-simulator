<?php

namespace App\Service;

use App\Entity\Proceso;


class SimuladorService
{
    function simular($memoria, $procesos) {

        $rafagas = []; //Array principal donde se van a almacenar todas las ráfagas
        $cola_nuevos = []; //Esta cola resguarda todos los procesos que piden cpu
        $cola_listos = []; //Esta cola guarda los procesos que entran en memoria

        //Cargar particiones de la memoria dentro de un array de particiones
        $particiones = $this->getParticionesArray($memoria);

        $condicionFin = true; //Condición de fin

        //Inicializo las ráfagas
        $t = 0;

        while ($condicionFin) {
            //Recorro para cargar en la cola de nuevos los procesos que tienen que arribar
            foreach ($procesos as $key => $proceso) {
                //Le doy formato de array al proceso
                $procesoFormateado = $this->serializarProceso($proceso, $key);
                if ($proceso->getTa() == $t) {
                    array_push($cola_nuevos, $procesoFormateado);
                }
            }
            //Recorro las particiones
            foreach ($particiones as $particionKey => $particion) {
                //Recorro los procesos
                foreach ($cola_nuevos as $procesoKey => $proceso) {
                    //Asigno si el proceso cabe en la particion y si tiene de status nuevo
                    if ($particiones[$particionKey]['proceso_asignado'] == null and
                        $proceso['size'] <= $particion and
                        $proceso['status'] == 'nuevo'
                    )
                    {
                        //Asigno listo al estado del proceso en la cola de nuevos
                        $cola_nuevos[$procesoKey]['status'] = 'listo';
                        //Asigno el proceso a la partición
                        $particiones[$particionKey]['proceso_asignado'] = $cola_nuevos[$procesoKey];
                        //Pongo el proceso en la cola de listos
                        array_push($cola_listos, $cola_nuevos[$procesoKey]);
                    }
                }
            }

            $rafagaActual = [
                'ejecutandose' => null,
                'finalizo' => null,
                'cola_nuevos' => null,
                'cola_listos' => null,
                'particiones' => null,
            ];
            $procesoEjecutado = false;
            $listoKey = 0;

            while (!$procesoEjecutado) {
                if (isset($cola_listos[$listoKey])) {
                    if ($cola_listos[$listoKey]['status'] == 'listo') {
                        if ($cola_listos[$listoKey]['ti'] > 0) {
                            $tiempo_remanente = $cola_listos[$listoKey]['ti'] -1;
                            $cola_listos[$listoKey]['ti'] = $tiempo_remanente;
                            $rafagaActual['ejecutandose'] = $cola_listos[$listoKey];

                            if ($tiempo_remanente == 0) {
                                $cola_listos[$listoKey]['status'] = 'finalizado';
                                $rafagaActual['finalizo'] = $cola_listos[$listoKey];
                            }
                        }
                        $procesoEjecutado = true;
                    }
                } else {
                    $procesoEjecutado = true;
                }

                ++$listoKey;
            }
            //Libero la memoria si finalizó algun proceso en la ráfaga actual
            if ($rafagaActual['finalizo']) {
                foreach ($particiones as $key => $particion) {
                    if ($rafagaActual['finalizo']['id'] == $particion['proceso_asignado']['id']) {
                        $particiones[$key]['proceso_asignado'] = null;
                    }
                }
            }

            //Seteo el estado de las colas para la ráfaga actual
            $rafagaActual['cola_nuevos'] = $cola_nuevos;
            $rafagaActual['cola_listos'] = $cola_listos;
            $rafagaActual['particiones'] = $particiones;

            //Agrego la ráfaga actual al array del total de ráfagas
            array_push($rafagas, $rafagaActual);

            //Pregunto si la cola de listos tiene todos los procesos ya cargadados
            if (count($cola_listos) == count($procesos)) {
                $termino = false;
                //Recorro la cola de listos
                foreach ($cola_listos as $proceso) {
                    if ($proceso['status'] == 'listo') {
                        //Si hay algún proceso listo todavía no hay que terminar
                        $termino = true;
                    }
                }
                //Si ninguno esta listo significa que todos finalizaron
                if (!$termino) {
                    $condicionFin = false;
                }
            }
            ++$t;
        }
        return $rafagas;
    }

    function getParticionesArray($memoria) {
        $particiones = [];
        foreach ($memoria->getParticiones() as $key => $particion) {
            //Le doy formato de array a cada partición
            $particionSerializada = $this->serializarParticion($particion, $key);

            //Agrego al array de particiones
            array_push($particiones, $particionSerializada);
        }

        return $particiones;

    }

    function serializarParticion($particion, $id) {
        return [
            'id' => $id,
            'size' => $particion->getSize(),
            'proceso_asignado' => null
        ];
    }

    function serializarProceso($proceso, $id) {
        return [
            'id' => $id,
            'size' => $proceso->getSize(),
            'ta' => $proceso->getTa(),
            'ti' => $proceso->getTi(),
            'status' => 'nuevo'
        ];
    }
}
