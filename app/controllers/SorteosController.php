<?php
require_once __DIR__ . '/../models/Sorteo.php';
require_once __DIR__ . '/../models/Participante.php';

class SorteosController
{
    public function index()
    {
        // Obtener todos los sorteos disponibles (abiertos y cerrados)
        $sorteos = Sorteo::obtenerTodos();

        // Mostrar vista de sorteos
        require __DIR__ . '/../views/sorteos/index.php';
    }

    public function crearSorteo()
    {
        if (isset($_POST['nombre'])) {
            $nombre = trim($_POST['nombre']);

            // Llamar al modelo para crear el sorteo
            $sorteo_id = Sorteo::crear($nombre);

            if ($sorteo_id) {
                // Enviar respuesta JSON con el ID del sorteo
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'sorteo_id' => $sorteo_id]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al crear el sorteo.']);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'El nombre del sorteo es requerido.']);
            exit;
        }
    }

    public function editarSorteo($sorteo_id)
    {
        // Obtener detalles del sorteo
        $sorteo = Sorteo::obtenerPorId($sorteo_id);

        if (!$sorteo) {
            echo "El sorteo no existe.";
            exit;
        }

        // Verificar si el sorteo está cerrado
        $cerrado = $sorteo['cerrado'];

        // Obtener participantes asociados al sorteo
        $participantes = Participante::obtenerPorSorteo($sorteo_id);

        // Mostrar la vista de edición del sorteo
        require __DIR__ . '/../views/sorteos/editar.php';
    }


    public function cargarParticipantes()
    {
        $sorteo_id = $_POST['sorteo_id'];
        $sorteo = Sorteo::obtenerPorId($sorteo_id);

        if ($sorteo['cerrado']) {
            echo "No puedes agregar participantes a un sorteo cerrado.";
            exit;
        }

        if ($_FILES['archivo']['tmp_name']) {
            $file = fopen($_FILES['archivo']['tmp_name'], 'r');
            fgetcsv($file); // Omitir encabezado

            // Procesar cada participante
            while (($data = fgetcsv($file, 1000, ",")) !== false) {
                $nombre = $data[0];
                $email = $data[1];
                $rut = $data[2];

                // Guardar o relacionar el participante con el sorteo
                Participante::guardarOAsociar($nombre, $email, $rut, $sorteo_id);
            }
            fclose($file);
        }

        header('Location: /sorteos');
        exit;
    }

    public function anularGanador()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $sorteo_id = $data['sorteo_id'];
        $participante_id = $data['participante_id'];

        try {
            // Verificar si el sorteo está cerrado
            $sorteo = Sorteo::obtenerPorId($sorteo_id);
            if (!$sorteo['cerrado']) {
                throw new Exception('El sorteo aún no ha sido cerrado.');
            }

            // Anular ganador
            Sorteo::anularGanador($sorteo_id, $participante_id);

            // Seleccionar un nuevo ganador
            $nuevoGanador = Sorteo::reemplazarGanador($sorteo_id, $participante_id);

            $message = 'El ganador ha sido reemplazado por ' . htmlspecialchars($nuevoGanador['nombre']) . '.';

            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }


    public function realizarSorteo()
    {
        header('Content-Type: application/json');

        try {
            $sorteo_id = $_POST['sorteo_id'];
            $tipo_sorteo = $_POST['tipo_sorteo'];

            $sorteo = Sorteo::obtenerPorId($sorteo_id);

            if ($sorteo['cerrado']) {
                echo json_encode(['success' => false, 'message' => 'El sorteo ya ha sido realizado.']);
                exit;
            }

            // Realizar el sorteo y obtener los resultados
            $resultados = Sorteo::realizar($sorteo_id, $tipo_sorteo);

            // Registrar ganadores y "al agua" si aplica, y cerrar el sorteo
            Sorteo::registrarGanadores($sorteo_id, $resultados);
            Sorteo::cerrarSorteo($sorteo_id);

            // Generar el HTML para mostrar en el SweetAlert
            ob_start();
            require __DIR__ . '/../views/sorteos/ganadores_ajax.php';
            $html = ob_get_clean();

            echo json_encode(['success' => true, 'html' => $html]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function publicar()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $sorteo_id = $data['sorteo_id'];

        try {
            Sorteo::publicar($sorteo_id);
            echo json_encode(['success' => true, 'message' => 'El sorteo ha sido publicado.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function despublicar()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $sorteo_id = $data['sorteo_id'];

        try {
            Sorteo::despublicar($sorteo_id);
            echo json_encode(['success' => true, 'message' => 'El sorteo ha sido despublicado.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function verResultado($sorteo_id)
    {
        $sorteo = Sorteo::obtenerPorId($sorteo_id);

        // Verificar si el sorteo existe
        if (!$sorteo) {
            // Redirigir a una página de error 404 si el sorteo no existe
            $title = 'Sorteo no encontrado';
            require __DIR__ . '/../views/404.php';
            exit;
        }

        if (!$sorteo['publicado']) {
            // Mostrar mensaje si el sorteo no está publicado
            $title = 'Resultado no disponible';
            require __DIR__ . '/../views/sorteos/no_publicado.php';
            exit;
        }

        if (!$sorteo['cerrado']) {
            // Si el sorteo no está cerrado, no hay resultados que mostrar
            $title = 'Resultado no disponible';
            require __DIR__ . '/../views/sorteos/no_publicado.php';
            exit;
        }

        // Obtener los ganadores, ganadores anulados y "al agua"
        $ganadores = Sorteo::obtenerGanadores($sorteo_id);
        $ganadoresAnulados = Sorteo::obtenerGanadoresAnulados($sorteo_id);
        $al_agua = Sorteo::obtenerAlAgua($sorteo_id);

        // Obtener la fecha y hora de cierre del sorteo
        $fecha_cierre = $sorteo['fecha_cierre'];

        $title = 'Resultado del Sorteo - ' . htmlspecialchars($sorteo['nombre']);
        require __DIR__ . '/../views/sorteos/resultado_publico.php';
    }
}
