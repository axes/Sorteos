<?php

require_once __DIR__ . '/../models/ApiKey.php';
require_once __DIR__ . '/../models/Participante.php';

class ApiController
{
    // Autenticación por API key
    private function autenticar($api_key)
    {
        // Verificar si la API key es válida
        return ApiKey::validarApiKey($api_key);
    }

    // Obtener concursos de un participante por RUT
    public function obtenerConcursosPorRut($rut)
    {
        // Validar si la API key está presente
        if (!isset($_GET['api_key'])) {
            $this->respuestaJson(false, 'API Key es requerida');
            return;
        }

        // Autenticar usando la API key
        $api_key_valida = $this->autenticar($_GET['api_key']);
        if (!$api_key_valida) {
            $this->respuestaJson(false, 'API Key inválida');
            return;
        }

        // Obtener los concursos en los que ha participado el usuario por RUT
        $concursos = Participante::obtenerConcursosPorRut($rut);

        if ($concursos && count($concursos) > 0) {
            // Devolver nombre y email del participante junto con los concursos
            $participante = [
                'nombre' => $concursos[0]['participante_nombre'],
                'email' => $concursos[0]['participante_email']
            ];

            // Eliminar los datos del participante de cada concurso (ya están en la respuesta principal)
            foreach ($concursos as &$concurso) {
                unset($concurso['participante_nombre'], $concurso['participante_email']);
            }

            // Respuesta JSON con éxito
            $this->respuestaJson(true, 'Concursos encontrados', [
                'participante' => $participante,
                'concursos' => $concursos
            ]);
        } else {
            $this->respuestaJson(false, 'No se encontraron concursos para este RUT');
        }
    }

    // Función para estandarizar la respuesta JSON
    private function respuestaJson($success, $message, $data = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit;
    }
}
