<?php
require_once __DIR__ . '/../models/Sorteo.php';
require_once __DIR__ . '/../models/Participante.php';

class SorteosController
{
    // Mostrar todos los sorteos disponibles en la página principal
    public function index()
    {
        $sorteos = Sorteo::obtenerTodos();
        require __DIR__ . '/../views/sorteos/index.php';
    }

    // Crear un nuevo sorteo
    public function crearSorteo()
    {
        if (isset($_POST['nombre'])) {
            $nombre = trim($_POST['nombre']);
            $sorteo_id = Sorteo::crear($nombre);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => $sorteo_id ? true : false,
                'sorteo_id' => $sorteo_id,
                'message' => $sorteo_id ? null : 'Error al crear el sorteo.'
            ]);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'El nombre del sorteo es requerido.']);
        exit;
    }

    // // Verificar la existencia de un sorteo (nuevo método)
    public function verificarExistenciaSorteo($sorteo_id)
    {
        $existe = Sorteo::obtenerPorId($sorteo_id) !== null;
        header('Content-Type: application/json');
        echo json_encode(['exists' => $existe]);
        exit;
    }

    // Editar sorteo existente
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

    // Cargar participantes a un sorteo
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
            fgetcsv($file); // Saltar encabezado

            while (($data = fgetcsv($file, 1000, ",")) !== false) {
                Participante::guardarOAsociar($data[0], $data[1], $data[2], $sorteo_id);
            }
            fclose($file);
        }

        header('Location: /sorteos');
        exit;
    }

    // Anular un ganador y seleccionar un reemplazo
    public function anularGanador()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $sorteo_id = $data['sorteo_id'];
        $participante_id = $data['participante_id'];

        try {
            $sorteo = Sorteo::obtenerPorId($sorteo_id);
            if (!$sorteo['cerrado']) {
                throw new Exception('El sorteo aún no ha sido cerrado.');
            }

            Sorteo::anularGanador($sorteo_id, $participante_id);
            $nuevoGanador = Sorteo::reemplazarGanador($sorteo_id, $participante_id);

            echo json_encode(['success' => true, 'message' => 'El ganador ha sido reemplazado por ' . htmlspecialchars($nuevoGanador['nombre']) . '.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Realizar el sorteo y registrar ganadores
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

            $resultados = Sorteo::realizar($sorteo_id, $tipo_sorteo);
            Sorteo::registrarGanadores($sorteo_id, $resultados);
            Sorteo::cerrarSorteo($sorteo_id);

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

    // Publicar el resultado del sorteo
    public function publicar()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $sorteo_id = $data['sorteo_id'];
    
        try {
            Sorteo::publicar($sorteo_id); // Este método debe establecer 'publicado' como 1 en la base de datos
            echo json_encode(['success' => true, 'message' => 'El sorteo ha sido publicado.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Despublicar el resultado del sorteo
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

    // Ver los resultados públicos de un sorteo
    public function verResultado($sorteo_id)
    {
        $sorteo = Sorteo::obtenerPorId($sorteo_id);

        if (!$sorteo) {
            $title = 'Sorteo no encontrado';
            require __DIR__ . '/../views/404.php';
            exit;
        }

        if (!$sorteo['publicado'] || !$sorteo['cerrado']) {
            $title = 'Resultado no disponible';
            require __DIR__ . '/../views/sorteos/no_publicado.php';
            exit;
        }

        $ganadores = Sorteo::obtenerGanadores($sorteo_id);
        $ganadoresAnulados = Sorteo::obtenerGanadoresAnulados($sorteo_id);
        $al_agua = Sorteo::obtenerAlAgua($sorteo_id);
        $fecha_cierre = $sorteo['fecha_cierre'];

        $title = 'Resultado del Sorteo - ' . htmlspecialchars($sorteo['nombre']);
        require __DIR__ . '/../views/sorteos/resultado_publico.php';
    }
}
