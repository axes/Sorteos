<?php
require_once __DIR__ . '/../app/controllers/ApiController.php';

// Definir el subdirectorio base de la aplicación
$basePath = '/sorteos'; // Ajusta esto según tu configuración
// Obtener la ruta solicitada y normalizarla
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Eliminar el subdirectorio base de la ruta
if ($basePath != '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
error_log("URI después de eliminar el basePath: " . $uri);

// Verificar si la solicitud es para la API
// if (preg_match('/^\/api\/concursos\/\d{1,8}-[0-9kK]$/', $uri)) {
//     $rut = substr($uri, strrpos($uri, '/') + 1);
//     $controller = new ApiController();
//     $controller->obtenerConcursosPorRut($rut);
//     exit;
// }
// Ruta para obtener concursos de un participante por RUT
if (preg_match('#^/api/concursos/([0-9\-kK]+)$#', $uri, $matches)) {
    $rut = $matches[1];
    $controller = new ApiController();
    $controller->obtenerConcursosPorRut($rut);
    exit;
}

require_once __DIR__ . '/../app/controllers/ParticipantesController.php';
require_once __DIR__ . '/../app/controllers/SorteosController.php';
require_once __DIR__ . '/auth.php';

// Ruta de login
// if ($uri == '/sorteos/login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
//     login($_POST['username'], $_POST['password']);
//     exit;
// } elseif ($uri == '/sorteos/login') {
//     require __DIR__ . '/../app/views/login.php';
//     exit;
// }
if ($uri == '/login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    login($_POST['username'], $_POST['password']);
    exit;
} elseif ($uri == '/login') {
    require __DIR__ . '/../app/views/login.php';
    exit;
}

// Verificar si el usuario está logueado antes de permitir acceso a las demás rutas
// if (!isLoggedIn() && strpos($uri, '/sorteos') === 0 && $uri != '/sorteos/login') {
//     header('Location: /sorteos/login');
//     exit;
// }
// if (!isLoggedIn() && strpos($uri, '/sorteos') === 0 && 
//     $uri != '/sorteos/login' && 
//     !preg_match('/^\/sorteos\/resultado\/\d+$/', $uri)) {
//     header('Location: /sorteos/login');
//     exit;
// }

// Rutas que no requieren autenticación
$publicRoutes = [
    '/login',
    '/api',
    '/resultado'
];

$isPublicRoute = false;
foreach ($publicRoutes as $publicRoute) {
    if (strpos($uri, $publicRoute) === 0) {
        $isPublicRoute = true;
        break;
    }
}

if (!isLoggedIn() && !$isPublicRoute) {
    header('Location: ' . $basePath . '/login');
    exit;
}

// Redirigir de /sorteos a /sorteos/sorteos si el usuario está logueado y accede a /sorteos
if ($uri == '/sorteos') {
    if (isLoggedIn()) {
        header('Location: /sorteos/sorteos');
        exit;
    } else {
        header('Location: /sorteos/login');
        exit;
    }
}

// Ruta para la página principal de sorteos
if ($uri == '/sorteos/sorteos') {
    $controller = new SorteosController();
    $controller->index();
    exit;
}

// Ruta para crear un sorteo
if ($uri == '/sorteos/crear' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new SorteosController();
    $controller->crearSorteo();
    exit;
}

// Ruta para editar un sorteo específico
if (preg_match('/^\/sorteos\/editar\/(\d+)$/', $uri, $matches)) {
    $sorteo_id = $matches[1];
    $controller = new SorteosController();
    $controller->editarSorteo($sorteo_id);
    exit;
}

// Ruta para manejar el formulario de edición y redirigir a la página de edición
if ($uri == '/sorteos/editar' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['sorteo_id'])) {
        $sorteo_id = $_GET['sorteo_id'];
        header('Location: /sorteos/editar/' . $sorteo_id);
        exit;
    } else {
        echo "No se ha seleccionado un sorteo.";
        exit;
    }
}

// Ruta para cargar participantes
if ($uri == '/sorteos/cargar-participantes') {
    $controller = new ParticipantesController();
    $controller->cargarParticipantes();
    exit;
}

// Ruta para anular un ganador
if ($uri == '/sorteos/anular-ganador' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new SorteosController();
    $controller->anularGanador();
    exit;
}

$basePath = '/sorteos'; // Cambia esto al subdirectorio donde está tu aplicación

// Ruta para banear participante
if ($uri == $basePath . '/participantes/banear' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new ParticipantesController();
    $controller->banearParticipante();

    exit;
}

// Ruta para desbanear participante
if ($uri == $basePath . '/participantes/desbanear' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new ParticipantesController();
    $controller->desbanearParticipante();

    exit;
}

// Ruta para realizar el sorteo
if ($uri == '/sorteos/realizar-sorteo') {
    $controller = new SorteosController();
    $controller->realizarSorteo();
    exit;
}

// Ruta para publicar el sorteo
if ($uri == '/sorteos/publicar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new SorteosController();
    $controller->publicar();
    exit;
}

// Ruta para despublicar el sorteo
if ($uri == '/sorteos/despublicar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new SorteosController();
    $controller->despublicar();
    exit;
}

// Ruta para ver el resultado del sorteo públicamente
if (preg_match('/^\/sorteos\/resultado\/(\d+)$/', $uri, $matches)) {
    $sorteo_id = $matches[1];
    $controller = new SorteosController();
    $controller->verResultado($sorteo_id);
    exit;
}

// Ruta para cerrar sesión
if ($uri == '/sorteos/logout') {
    logout();
    exit;
}

// Ruta para obtener concursos de un participante por RUT
if (preg_match('/^\/api\/concursos\/([0-9\-]+)$/', $uri, $matches)) {
    $rut = $matches[1];
    $controller = new ApiController();
    $controller->obtenerConcursosPorRut($rut);
    exit;
}

// Manejo de 404 para rutas no válidas
header("HTTP/1.0 404 Not Found");
require __DIR__ . '/../app/views/404.php';
exit;
