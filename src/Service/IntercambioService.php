<?php

namespace App\Service;

class IntercambioService
{
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

    function liberarProcesoDeMemoria($proceso, $particiones){
        foreach ($particiones as $key => $particion) {
            if ($particion['proceso_asignado']['id'] == $proceso['id'] ) {
                $particiones[$key]['proceso_asignado'] = null;
            }
        }
        return $particiones;
    }

    function finalizoSimulador($cola_listos, $cola_bloqueados, $cola_nuevos) {

        return !(count($cola_listos) || count($cola_bloqueados) || count($cola_nuevos));
    }
}
