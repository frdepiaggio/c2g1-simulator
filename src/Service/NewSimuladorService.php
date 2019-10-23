<?php

namespace App\Service;

class NewSimuladorService
{
    function validarFormMemoria($array) {
        $response = [
            'code' => 200,
            'mensaje' => 'ok',
            'memoria' => null,
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
            foreach ($array['particiones'] as $particionSize ) {
                $particionesTotalSize = $particionesTotalSize + $particionSize;
            }
            if ($particionesTotalSize <> $availableMemoria) {
                $response['code'] = 400;
                $response['mensaje'] = 'error';
                array_push($response['error'], 'particiones_size');
            }
        }
        return $response;
    }
}
