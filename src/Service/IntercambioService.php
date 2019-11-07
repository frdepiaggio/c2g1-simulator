<?php

namespace App\Service;

class IntercambioService
{
    /*
     * Esta función se encarga de llamar a las funciones correspondientes dependiendo
     * del tipo de memoria y algoritmo de intercambio de la simulación.
     * */
    function asignacionMemoria($cola_listos, $cola_nuevos, $particiones, $algoritmo, $tipo)
    {
        if ($tipo == 'variables') {
            $particiones = $this->unirParticiones($particiones);
        }

        if ($algoritmo == 'ff') {
            list($cola_listos, $cola_nuevos, $particiones) =
                $this->firstFit($cola_listos, $cola_nuevos, $particiones, $tipo)
            ;
        } elseif ($algoritmo == 'bf') {
            list($cola_listos, $cola_nuevos, $particiones) =
                $this->bestFit($cola_listos, $cola_nuevos, $particiones)
            ;
        } else {
            dd('no hay worst-fit');
        }

        return [$cola_listos, $cola_nuevos, $particiones];
    }

    /*
     * Esta función desasigna el proceso de la partición.
     * Buscando dicho proceso en el array de particiones
     * */
    function liberarProcesoDeMemoria($proceso, $particiones)
    {
        foreach ($particiones as $key => $particion) {
            if ($particion['proceso_asignado']['id'] == $proceso['id'] ) {
                $particiones[$key]['proceso_asignado'] = null;
            }
        }

        return $particiones;
    }

    /*
     * Esta función permite unir las particiones vacias que
     * se encuentran contiguas en memoria
     * */
    function unirParticiones($particiones)
    {
        //Recorro las particiones
        foreach ($particiones as $particionKey => $particion) {
            /*
             * Pregunto si hay una siguiente particion y si,
             * ni la actual ni la siguiente tienen un proceso asignado
             * */
            if (isset($particiones[$particionKey+1]) &&
                $particiones[$particionKey+1]['proceso_asignado'] == null &&
                $particiones[$particionKey]['proceso_asignado'] == null)
            {
                //A la particion actual le sumo el tamaño de ambas particiones
                $particiones[$particionKey]['size'] =
                    $particiones[$particionKey]['size'] + $particiones[$particionKey +1]['size'];

                //Elimino la siguiente partición
                unset($particiones[$particionKey +1]);
                //Reseteo los indices del array $particiones
                $particiones = array_values($particiones);

                //Vuelvo a llamar a la función con las particiones actualizadas
                return $this->unirParticiones($particiones);
            }
        }

        return $particiones;
    }

    /*
     * Esta función permite, dados el array de particiones, la posicion de una
     * particion y un proceso, asignar dicho proceso a la particion requerida y actualizando
     * el esquema de particiones variables.
     * */
    function actualizarParticionesVariables($particiones, $particionTargetKey, $proceso)
    {
        /*
         * Si el tamaño del proceso es exactamente igual al de la particion objetivo
         * solo se asignará el proceso a dicha partición.
        */
        if ($proceso['size'] == $particiones[$particionTargetKey]['size']) {
            $particiones[$particionTargetKey]['proceso_asignado'] = $proceso;
        } elseif ($proceso['size'] < $particiones[$particionTargetKey]['size']) { //Si es menor
            //Se crea una nueva particion
            $nuevaParticion = $particiones[$particionTargetKey];
            //Esto se realiza para que la nueva partición quede contigua a la objetivo cuando ordenemos el array
            $nuevaParticion['id'] = $particiones[$particionTargetKey]['id'] + 0.5;
            $nuevaParticion['size'] = $proceso['size']; //Se le asigna el mismo tamaño del proceso
            $nuevaParticion['proceso_asignado'] = $proceso; //El proceso es asignado a la nueva partición

            //Se actualiza el tamaño de la partición objetivo restandole el de la nueva
            $particiones[$particionTargetKey]['size'] =
                $particiones[$particionTargetKey]['size'] - $nuevaParticion['size'];

            //Se agrega la nueva particion al array de particiones, quedando ésta, en última posición
            array_push($particiones, $nuevaParticion);

            //Se ordena el array de particiones por id, quedando la nueva particion inmediatamente
            //después de la particion objetivo actualizada, ya que tiene su mismo id + 0.5
            usort($particiones, function ($a, $b) {
                return ($a['id'] < $b['id']) ? -1 : 1;
            });

            //Se actualizan los id (para evitar que la nueva partición no tenga un entero como id)
            foreach ($particiones as $key => $particion) {
                $particiones[$key]['id'] = $key;
            }
        }

        return $particiones;
    }

    /*
     * Esta función permite, dados el array de particiones, la posicion de una
     * particion y un proceso, asignar dicho proceso a la particion requerida y actualizando
     * el esquema de particiones fijas.
     * */
    function actualizarParticionesFijas($particiones, $particionTargetKey, $proceso)
    {
        //Se asigna el proceso a la partición
        $particiones[$particionTargetKey]['proceso_asignado'] = $proceso;

        return $particiones;
    }

    /*
     * Esta función permite gestionar el algorimo de intercambio "First-Fit"
     * */
    function firstFit($cola_listos, $cola_nuevos, $particiones, $tipo) {
        //Recorro las particiones
        foreach ($particiones as $particionKey => $particion) {
            //Recorro los procesos
            foreach ($cola_nuevos as $procesoKey => $proceso) {
                //Asigno si el proceso cabe en la particion y si tiene de status nuevo
                if ($particiones[$particionKey]['proceso_asignado'] == null and
                    $proceso['size'] <= $particion['size']
                ) {
                    if ($tipo == 'fijas') {
                        $particionesNuevas =
                            $this->actualizarParticionesFijas($particiones, $particionKey, $proceso);
                    } else {
                        $particionesNuevas =
                            $this->actualizarParticionesVariables($particiones, $particionKey, $proceso);
                    }
                    //Asigno el proceso a la partición
                    $particiones = $particionesNuevas;
                    //Pongo el proceso en la cola de listos
                    array_push($cola_listos, $cola_nuevos[$procesoKey]);
                    //Saco el proceso de la cola de nuevos
                    unset($cola_nuevos[$procesoKey]);
                    //Si el tipo de memoria es de "particiones variables" se vuelve a llamar a la función
                    if ($tipo == 'variables') {
                        return $this->firstFit($cola_listos, $cola_nuevos, $particionesNuevas, $tipo);
                    }
                }
            }
        }
        return [$cola_listos, $cola_nuevos, $particiones];
    }

    function buscarElementoKey($id, $array) {
        foreach ($array as $key => $elemento) {
            if ($elemento['id'] == $id) {
                return $key;
            }
        }
        return null;
    }

    /*
     * Esta función permite gestionar el algorimo de intercambio "First-Fit"
     * */
    function bestFit($cola_listos, $cola_nuevos, $particiones) {
        //Creo arrays auxiliares de particiones y cola de nuevos
        $particionesAuxiliar = $particiones;
        $colaNuevosAuxiliar = $cola_nuevos;

        //Ordeno las particiones de menor a mayor y los procesos de mayor a menor
        usort($particionesAuxiliar, function ($a, $b) {
            return ($a['size'] < $b['size']) ? -1 : 1;
        });
        usort($colaNuevosAuxiliar, function ($a, $b) {
            return ($a['size'] > $b['size']) ? -1 : 1;
        });

        //Recorro las particiones auxiliares
        foreach ($particionesAuxiliar as $particionAuxKey => $particion) {
            //Recorro los procesos auxiliares
            foreach ($colaNuevosAuxiliar as $procesoAuxKey => $proceso) {
                //Si el proceso auxiliar cabe en la particion auxiliar y si tiene de status nuevo
                if ($particionesAuxiliar[$particionAuxKey]['proceso_asignado'] == null and
                    $proceso['size'] <= $particion['size'])
                {
                    //Busco las posiciones del proceso y la particion en los arrays originales
                    $particionKeyReal =
                        $this->buscarElementoKey($particionesAuxiliar[$particionAuxKey]['id'], $particiones);
                    $procesoKeyReal =
                        $this->buscarElementoKey($colaNuevosAuxiliar[$procesoAuxKey]['id'], $cola_nuevos);

                    //Pregunto si encontró ambos
                    if (!is_null($procesoKeyReal) && !is_null($particionKeyReal)) {
                        //Hago la asignación de la misma manera que en First-Fit
                        $procesoReal = $cola_nuevos[$procesoKeyReal];
                        $particionesNuevas =
                            $this->actualizarParticionesFijas($particiones, $particionKeyReal, $procesoReal);
                        //Asigno el proceso a la partición
                        $particiones = $particionesNuevas;
                        //Pongo el proceso en la cola de listos
                        array_push($cola_listos, $cola_nuevos[$procesoKeyReal]);
                        //Saco el proceso de la cola de nuevos
                        unset($cola_nuevos[$procesoKeyReal]);

                        $cola_nuevos = array_values($cola_nuevos);
                    }
                }
            }
        }
        return [$cola_listos, $cola_nuevos, $particiones];
    }
}
