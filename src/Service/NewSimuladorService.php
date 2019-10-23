<?php

namespace App\Service;

class NewSimuladorService
{
    function validarFormMemoria($array) {
        $response = [
            'code' => 200,
            'mensaje' => 'ok',
            'memoria' => null
        ];
        if ($array['totalSize'] == 'NaN' || $array['totalSize'] < 0) {
            $response['code'] = 400;
            $response['mensaje'] = 'totalSize';
        } elseif ($array['soSize'] == 'NaN' || $array['soSize'] < 0 || $array['soSize'] > ($array['totalSize']/2)) {
            $response['code'] = 400;
            $response['mensaje'] = 'soSize';
        } elseif ($array['tipo'] == '') {
            $response['code'] = 400;
            $response['mensaje'] = 'tipo';
        } elseif (!isset($array['particiones'])) {
            $response['code'] = 400;
            $response['mensaje'] = 'particiones_null';
        } else {
            $particionesTotalSize = 0;
            $availableMemoria = $array['totalSize'] - $array['soSize'];
            foreach ($array['particiones'] as $particionSize ) {
                $particionesTotalSize = $particionesTotalSize + $particionSize;
            }
            if ($particionesTotalSize <> $availableMemoria) {
                $response['code'] = 400;
                $response['mensaje'] = 'particiones_size';
            }
        }
        return $response;
    }
}
