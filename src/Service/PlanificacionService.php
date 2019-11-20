<?php

namespace App\Service;

class PlanificacionService
{
    private $intercambioService;

    public function __construct(IntercambioService $intercambioService)
    {
        $this->intercambioService = $intercambioService;
    }

    function fcfs($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tipoMemoria) {
        if (!empty($cola_listos)) {
            $procesoEnTratamiento = $cola_listos[0];
            $ciclo = $procesoEnTratamiento['ciclo'];
            $rafagaActual['ejecuto'] = $procesoEnTratamiento; //Cargar proceso ejecutado

            if ($ciclo[0]['tipo'] == 'irrupcion') {
                $tiempo_remanente = $ciclo[0]['valor'] - 1;

                if ($tiempo_remanente == 0 && isset($ciclo[1])) { //Si se termina la irrupcion y viene un bloqueo

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino
                    array_push($cola_bloqueados, $procesoEnTratamiento);

                    $rafagaActual['bloqueo'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                } else if ($tiempo_remanente == 0 && !isset($ciclo[1])) { // Si termina la irrupción y termina el proceso

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el ciclo del proceso
                    $rafagaActual['finalizo'] = $procesoEnTratamiento; //Cargar proceso finalizado

                } else {
                    //El proceso se ejecuta normalmente y sigue en CPU
                    $ciclo[0]['valor'] = $tiempo_remanente; //Se resta la irrupcion
                    $cola_listos[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de listos
                }
            }
        }

        return [array_values($cola_listos), array_values($cola_bloqueados), $particiones, $rafagaActual];
    }

    function rr($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $quantum, $tipoMemoria) {
        if (!empty($cola_listos)) {
            $procesoEnTratamiento = $cola_listos[0];
            $ciclo = $procesoEnTratamiento['ciclo'];
            $quantumProceso = $procesoEnTratamiento['quantum'];
            $rafagaActual['ejecuto'] = $procesoEnTratamiento; //Cargar proceso ejecutado

            if ($ciclo[0]['tipo'] == 'irrupcion') {
                $tiempo_remanente = $ciclo[0]['valor'] - 1;
                $tiempo_remanente_quantum = $quantumProceso -1;

                if ($tiempo_remanente == 0 && isset($ciclo[1])) { //Si se termina la irrupcion y viene un bloqueo

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino
                    $procesoEnTratamiento['quantum'] = $quantum;
                    array_push($cola_bloqueados, $procesoEnTratamiento);

                    $rafagaActual['bloqueo'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                } else if ($tiempo_remanente == 0 && !isset($ciclo[1])) { // Si termina la irrupción y termina el proceso

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el ciclo del proceso
                    $rafagaActual['finalizo'] = $procesoEnTratamiento; //Cargar proceso finalizado

                } else if ($tiempo_remanente_quantum == 0) { //Si se termina el quantum

                    $ciclo[0]['valor'] = $tiempo_remanente; //Se resta la irrupcion
                    $procesoEnTratamiento['ciclo'] = $ciclo;
                    $procesoEnTratamiento['quantum'] = $quantum; //Se resetea el quantum del proceso

                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    array_push($cola_listos, $procesoEnTratamiento); //Se lo vuelve a poner al final de la cola de listos

                    $rafagaActual['interrumpe'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                } else {
                    //El proceso se ejecuta normalmente y sigue en CPU
                    $ciclo[0]['valor'] = $tiempo_remanente; //Se resta la irrupcion
                    $cola_listos[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de listos
                    $cola_listos[0]['quantum'] = $tiempo_remanente_quantum;
                }
            }
        }

        return [array_values($cola_listos), array_values($cola_bloqueados), $particiones, $rafagaActual];
    }

    function tratarBloqueados($cola_bloqueados, $cola_nuevos, $rafaga) {
        if (!empty($cola_bloqueados)) {
            $procesoEnTratamiento = $cola_bloqueados[0];
            $ciclo = $cola_bloqueados[0]['ciclo'];
            $rafaga['ejecuto_es'] = $procesoEnTratamiento;

            if ($ciclo[0]['tipo'] == 'bloqueo') {
                $bloqueo_remanente = $ciclo[0]['valor'] - 1;

                if ($bloqueo_remanente == 0 ) { //Si se termina la irrupcion y viene un bloqueo
                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_bloqueados[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino
                    $rafaga['finalizo_es'] = $procesoEnTratamiento;
                    array_push($cola_nuevos, $procesoEnTratamiento); //El proceso vuelve a la cola de nuevos a competir por memoria

                } else {
                    //El proceso se ejecuta normalmente y sigue en E/S
                    $ciclo[0]['valor'] = $bloqueo_remanente; //Se resta la irrupcion
                    $cola_bloqueados[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de bloqueados

                }
            }
        }

        return [array_values($cola_bloqueados), array_values($cola_nuevos), $rafaga];
    }

    function sjf($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tipoMemoria) {
        foreach ($cola_listos as $key => $proceso) {
            if (!isset($proceso['irrupcion_orden'])) {
                $cola_listos[$key]['irrupcion_orden'] = $cola_listos[$key]['ciclo'][0]['valor'];
            }
        }

        usort($cola_listos, function ($a, $b) {
            return ($a['irrupcion_orden'] < $b['irrupcion_orden']) ? -1 : 1;
        });

        if (!empty($cola_listos)) {
            $procesoEnTratamiento = $cola_listos[0];
            $ciclo = $procesoEnTratamiento['ciclo'];
            $rafagaActual['ejecuto'] = $procesoEnTratamiento; //Cargar proceso ejecutado

            if ($ciclo[0]['tipo'] == 'irrupcion') {
                $tiempo_remanente = $ciclo[0]['valor'] - 1;

                if ($tiempo_remanente == 0 && isset($ciclo[1])) { //Si se termina la irrupcion y viene un bloqueo

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino

                    /*
                     * Quito el campo "irrupcion_orden" para que se vuelva a
                     * ordenar la cola de listos en la siguiente irrupcion al procesador
                     * */
                    unset($procesoEnTratamiento['irrupcion_orden']);

                    array_push($cola_bloqueados, $procesoEnTratamiento);
                    $rafagaActual['bloqueo'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                } else if ($tiempo_remanente == 0 && !isset($ciclo[1])) { // Si termina la irrupción y termina el proceso

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el ciclo del proceso
                    $rafagaActual['finalizo'] = $procesoEnTratamiento; //Cargar proceso finalizado

                } else {
                    //El proceso se ejecuta normalmente y sigue en CPU
                    $ciclo[0]['valor'] = $tiempo_remanente; //Se resta la irrupcion
                    $cola_listos[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de listos
                }
            }
        }

        return [array_values($cola_listos), array_values($cola_bloqueados), $particiones, $rafagaActual];
    }

    function srtf($cola_listos, $cola_bloqueados, $particiones, $rafagaActual, $tipoMemoria) {
        usort($cola_listos, function ($a, $b) {
            return ($a['ciclo'][0]['valor'] < $b['ciclo'][0]['valor']) ? -1 : 1;
        });
        if (!empty($cola_listos)) {
            $procesoEnTratamiento = $cola_listos[0];
            $ciclo = $procesoEnTratamiento['ciclo'];
            $rafagaActual['ejecuto'] = $procesoEnTratamiento; //Cargar proceso ejecutado

            if ($ciclo[0]['tipo'] == 'irrupcion') {
                $tiempo_remanente = $ciclo[0]['valor'] - 1;

                if ($tiempo_remanente == 0 && isset($ciclo[1])) { //Si se termina la irrupcion y viene un bloqueo

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el proceso sin la irrupción que termino
                    array_push($cola_bloqueados, $procesoEnTratamiento);
                    $rafagaActual['bloqueo'] = $procesoEnTratamiento; //Cargar proceso ejecutado

                } else if ($tiempo_remanente == 0 && !isset($ciclo[1])) { // Si termina la irrupción y termina el proceso

                    unset($ciclo[0]); //Sacar la irrupción que llego a cero del ciclo
                    unset($cola_listos[0]); //Sacar el proceso de la cola de listos
                    $procesoEnTratamiento['ciclo'] = array_values($ciclo); //Actualizar el ciclo del proceso
                    $rafagaActual['finalizo'] = $procesoEnTratamiento; //Cargar proceso finalizado

                } else {
                    //El proceso se ejecuta normalmente y sigue en CPU
                    $ciclo[0]['valor'] = $tiempo_remanente; //Se resta la irrupcion
                    $cola_listos[0]['ciclo'] = $ciclo; //Se actualiza el ciclo en la cola de listos
                    usort($cola_listos, function ($a, $b) {
                        return ($a['ciclo'][0]['valor'] < $b['ciclo'][0]['valor']) ? -1 : 1;
                    });
                }
            }
        }

        return [array_values($cola_listos), array_values($cola_bloqueados), $particiones, $rafagaActual];
    }
}
