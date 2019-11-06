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

    function liberarProcesoDeMemoria($proceso, $particiones)
    {
        foreach ($particiones as $key => $particion) {
            if ($particion['proceso_asignado']['id'] == $proceso['id'] ) {
                $particiones[$key]['proceso_asignado'] = null;
            }
        }

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
                $particiones[$particionKey]['size'] =
                    $particiones[$particionKey]['size'] + $particiones[$particionKey +1]['size'];

                unset($particiones[$particionKey +1]);
                $particiones = array_values($particiones);

                return $this->unirParticiones($particiones);
            }
        }

        return $particiones;
    }

    function actualizarParticionesVariables($particiones, $particionTargetKey, $proceso)
    {
        if ($proceso['size'] == $particiones[$particionTargetKey]['size']) {
            $particiones[$particionTargetKey]['proceso_asignado'] = $proceso;
        } elseif ($proceso['size'] < $particiones[$particionTargetKey]['size']) {
            $nuevaParticion = $particiones[$particionTargetKey];
            $nuevaParticion['id'] = $particiones[$particionTargetKey]['id'] + 0.5;
            $nuevaParticion['size'] = $proceso['size'];
            $nuevaParticion['proceso_asignado'] = $proceso;

            $particiones[$particionTargetKey]['size'] =
                $particiones[$particionTargetKey]['size'] - $nuevaParticion['size'];

            array_push($particiones, $nuevaParticion);

            usort($particiones, function ($a, $b) {
                return strcmp($a["id"], $b["id"]);
            });

            foreach ($particiones as $key => $particion) {
                $particiones[$key]['id'] = $key;
            }
        }

        return $particiones;
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
                            $this->actualizarParticionesVariables($particiones, $particionKey, $proceso)
                        ;
                    }
                    //Asigno el proceso a la partición
                    $particiones = $particionesNuevas;
                    //Pongo el proceso en la cola de listos
                    array_push($cola_listos, $cola_nuevos[$procesoKey]);
                    //Saco el proceso de la cola de nuevos
                    unset($cola_nuevos[$procesoKey]);

                    if ($tipo == 'variables') {
                        return $this->ff($cola_listos, $cola_nuevos, $particionesNuevas, $tipo);
                    }
                }
            }
        }
        return [$cola_listos, $cola_nuevos, $particiones];
    }
}
