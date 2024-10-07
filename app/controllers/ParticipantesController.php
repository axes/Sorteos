<?php
require_once __DIR__ . '/../models/Participante.php';

class ParticipantesController
{
    public function cargarParticipantes()
    {
        header('Content-Type: application/json');
        $sorteo_id = $_POST['sorteo_id'];

        // Verificar si el sorteo está cerrado
        $sorteo = Sorteo::obtenerPorId($sorteo_id);
        if ($sorteo['cerrado']) {
            echo json_encode(['success' => false, 'message' => 'No puedes agregar participantes a un sorteo cerrado.']);
            exit;
        }

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
            $archivo = $_FILES['archivo']['tmp_name'];
            $resultado = Participante::cargarDesdeCSV($archivo, $sorteo_id);

            // Obtener la lista actualizada de participantes
            $participantes = Participante::obtenerPorSorteo($sorteo_id);

            $mensaje = "Se procesaron {$resultado['totalProcesados']} registros. ";
            $mensaje .= "{$resultado['totalNuevos']} participantes nuevos fueron registrados para este sorteo.";

            echo json_encode([
                'success' => true,
                'message' => $mensaje,
                'participantes' => $participantes
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cargar el archivo.']);
            exit;
        }
    }

    public function banearParticipante()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $participante_id = $data['participante_id'];

        try {
            Participante::banear($participante_id);
            echo json_encode(['success' => true, 'message' => 'El participante ha sido baneado.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function desbanearParticipante()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $participante_id = $data['participante_id'];

        try {
            Participante::desbanear($participante_id);
            echo json_encode(['success' => true, 'message' => 'El participante ha sido desbaneado.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
