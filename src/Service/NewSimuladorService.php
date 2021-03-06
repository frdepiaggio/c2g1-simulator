<?php

namespace App\Service;

class NewSimuladorService
{
    function validarFormMemoria($array) {
        $response = [
            'code' => 200,
            'mensaje' => 'ok',
            'newMemoriaId' => null,
            'newSimuladorId' => null,
            'maximaParticionSize' => null,
            'error' => []
        ];
        if ($array['totalSize'] == 'NaN' || $array['totalSize'] < 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'totalSize');
        }
        if ($array['soSize'] == 'NaN' || $array['soSize'] < 0 || $array['soSize'] > ($array['totalSize']/2)) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'soSize');
        }
        if ($array['tipo'] == '') {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'tipo');
        }
        if (!isset($array['particiones'])) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'particiones_null');
        }
        if (!in_array('totalSize', $response['error']) && !in_array('soSize', $response['error'])) {
            $particionesTotalSize = 0;
            $availableMemoria = $array['totalSize'] - $array['soSize'];
            foreach ($array['particiones'] as $particionArray ) {
                $particionesTotalSize = $particionesTotalSize + $particionArray['size'];
            }
            if ($particionesTotalSize <> $availableMemoria) {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'particiones_size');
            }
        }
        return $response;
    }
    function validarFormProceso($array) {
        $response = [
          'code' => 200,
          'mensaje' => 'ok',
          'newProcesoId' => null,
          'error' => []
        ];
        if ($array['ta'] == 'NaN' || $array['ta'] < 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'ta');
        }
        if ($array['ti1'] == 'NaN' || $array['ti1'] <= 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'ti1');
        }
        if ($array['es'] == 'NaN' || $array['es'] <= 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'es');
        }
        if ($array['ti2'] == 'NaN' || $array['ti2'] <= 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'ti2');
        }
        if ($array['size'] == 'NaN' || $array['size'] <= 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'size_null');
        } elseif ($array['size'] > $array['maximo-size-particion']) {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'size_max');
        }
        if ($array['algoritmo_planificacion'] == 'multinivel' && $array['prioridad'] == 'default') {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'prioridad');
        }
        return $response;
    }

    function validarFormSimulador($array) {
        $response = [
          'code' => 200,
          'mensaje' => 'ok',
          'simulador' => null,
          'error' => []
        ];
        if ($array['algoritmo_planificacion'] == 'default') {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'algoritmo_planificacion');
        }
        if ($array['algoritmo_planificacion'] == 'rr' && $array['quantum'] == 'default') {
            $response['code'] = 400;
            $response['mensaje'] = 'error';
            array_push($response['error'], 'quantum');
        }
        if ($array['algoritmo_planificacion'] == 'multinivel') {
            if ($array['cola_alta'] == 'rr' && $array['cola_alta_quantum'] == 'default') {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'cola_alta_quantum');
            }
            if ($array['cola_media'] == 'rr' && $array['cola_media_quantum'] == 'default') {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'cola_media_quantum');
            }
            if ($array['cola_baja'] == 'rr' && $array['cola_baja_quantum'] == 'default') {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'cola_baja_quantum');
            }
            if ($array['cola_alta'] == 'default') {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'cola_alta');
            }
            if ($array['cola_media'] == 'default') {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'cola_media');
            }
            if ($array['cola_baja'] == 'default') {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'cola_baja');
            }
        }
        return $response;
    }
}
