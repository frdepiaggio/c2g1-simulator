<?php

namespace App\Service;

class IntercambioService
{
    function asignacionParticionesFijas($cola_listos, $cola_nuevos, $particiones, $algoritmo = null)
    {
        if ($algoritmo == 'ff') {
            list($cola_listos, $cola_nuevos, $particiones) =
                $this->ff($cola_listos, $cola_nuevos, $particiones, 'fijas')
            ;
        } elseif ($algoritmo == 'bf') {
            dd('no hay best-fit');
        } else {
            dd('no hay worst-fit');
        }

        return [$cola_listos, $cola_nuevos, $particiones];
    }

    function asignacionParticionesVariables($cola_listos, $cola_nuevos, $particiones, $algoritmo = null)
    {
        if ($algoritmo == 'ff') {
            list($cola_listos, $cola_nuevos, $particiones) =
                $this->ff($cola_listos, $cola_nuevos, $particiones, 'variables')
            ;
        } elseif ($algoritmo == 'bf') {
            dd('no hay best-fit');
        } else {
            dd('no hay worst-fit');
        }

        return [$cola_listos, $cola_nuevos, $particiones];
    }

    function liberarProcesoDeMemoria($proceso, $particiones, $tipo)
    {
        foreach ($particiones as $key => $particion) {
            if ($particion['proceso_asignado']['id'] == $proceso['id'] ) {
                $particiones[$key]['proceso_asignado'] = null;
            }
        }
//        if ($tipo == 'variables' and isset($keyTarget)) {
//            dd($particiones);
//            $particionesNuevas = $this->unirParticiones($particiones);
//            $particiones = $particionesNuevas;
//        }
        return $particiones;
    }

    function dividirParticion($particion, $proceso)
    {
        $sizeActual = $particion['size'];
        $sizeProceso = $proceso['size'];

        $particionVieja = $particion;
        $particionVieja['size'] = $sizeActual - $sizeProceso;

        $particionNueva = [
            'id' => $particion['id'] +1,
            'size' => $proceso['size'],
            'proceso_asignado' => $proceso
        ];
        return [$particionVieja, $particionNueva];
    }

    function unirParticiones($particiones)
    {
        foreach ($particiones as $particionKey => $particion) {
            if (isset($particiones[$particionKey+1]) &&
                $particiones[$particionKey+1]['proceso_asignado'] == null &&
                $particiones[$particionKey]['proceso_asignado'] == null
            ) {
                $particionesNuevas = [];
                $nuevaParticion = $particion;
                $nuevaParticion['size'] = $particiones[$particionKey]['size'] + $particiones[$particionKey+1]['size'];

                array_push($particionesNuevas, $nuevaParticion);
                if (isset($particiones[$particionKey+2])) {
                    $particionesPosteriores = array_slice($particiones, $particionKey+2);
                    foreach ($particionesPosteriores as $p) {
                        array_push($particionesNuevas, $p);
                    }
                }
                $this->unirParticiones(array_values($particionesNuevas));
            }
        }

        return $particiones;
    }

    function actualizarParticionesVariables($particiones, $particionTarget, $particionTargetKey, $proceso)
    {
        $particionesNuevas = []; //Inicializo un array para las nuevas particiones resultantes
        //Pregunto si existe algun elemento anterior
        if (isset($particiones[$particionTargetKey - 1])) {
            //Extraigo la porcion de array anterior a la particion encontrada
            $particionesAnteriores = array_slice($particiones, 0, $particionTargetKey-1);
            foreach ($particionesAnteriores as $particionAnterior) {
                //Inserto dichas particiones dentro del array de particiones nuevas
                array_push($particionesNuevas, $particionAnterior);
            }
        }
        //Divido la particion
        list($particionActualizada, $particionNueva) = $this->dividirParticion($particionTarget, $proceso);
        //Inserto la particion actualizada y la nueva dentro del array de particiones nuevas
        array_push($particionesNuevas, $particionActualizada);
        array_push($particionesNuevas, $particionNueva);

        //Pregunto si existe algun elemento posterior
        if (isset($particiones[$particionTargetKey + 1])) {
            //Extraigo la porcion de array posterior a la particion encontrada
            $particionesPosteriores = array_slice($particiones, $particionTargetKey+1);
            foreach ($particionesPosteriores as $particionPosterior) {
                //Aumento el id en 1 de la particion e inserto en el array de particiones nuevas
                $particionPosterior['id'] = $particionPosterior['id'] + 1;
                array_push($particionesNuevas, $particionPosterior);
            }
            if (count($particiones) > 2 ) {
                dd($particiones, '-----------', $particionActualizada, $particionNueva, $particionesPosteriores, $particionesNuevas);
            }
        }
        return $particionesNuevas;
    }

    function actualizarparticionesFijas($particiones, $key, $proceso)
    {
        $particiones[$key]['proceso_asignado'] = $proceso;

        return $particiones;
    }

    function ff($cola_listos, $cola_nuevos, $particiones, $tipo) {
        //Recorro las particiones
        foreach ($particiones as $particionKey => $particion) {
            //Recorro los procesos
            foreach ($cola_nuevos as $procesoKey => $proceso) {
                //Asigno si el proceso cabe en la particion y si tiene de status nuevo
                if ($particiones[$particionKey]['proceso_asignado'] == null and
                    $proceso['size'] <= $particion['size']
                ) {
                    if ($tipo == 'fijas') {
                        $particionesNuevas = $this->actualizarparticionesFijas($particiones, $particionKey, $proceso);
                    } else {
                        $particionesNuevas =
                            $this->actualizarParticionesVariables($particiones, $particion, $particionKey, $proceso)
                        ;
                    }
                    //Asigno el proceso a la partición
                    $particiones = $particionesNuevas;
                    //Pongo el proceso en la cola de listos
                    array_push($cola_listos, $cola_nuevos[$procesoKey]);
                    //Saco el proceso de la cola de nuevos
                    unset($cola_nuevos[$procesoKey]);

                    $this->ff($cola_listos, $cola_nuevos, $particionesNuevas, $tipo);
                }
            }
        }
        return [$cola_listos, $cola_nuevos, $particiones];
    }
}
