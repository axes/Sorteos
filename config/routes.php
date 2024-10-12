<?php

require_once __DIR__ . '/../app/controllers/ApiController.php';
require_once __DIR__ . '/../app/controllers/ParticipantesController.php';
require_once __DIR__ . '/../app/controllers/SorteosController.php';
require_once __DIR__ . '/auth.php';

// Definir el subdirectorio base de la aplicación
$basePath = ''; // Ajusta según la configuración

// Obtener y normalizar la ruta solicitada
$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
error_log("URI después de eliminar el basePath: " . $uri);

// Rutas públicas
$publicRoutes = ['/login', '/api', '/sorteos/resultado'];

// Comprobar si la ruta es pública
$isPublicRoute = array_reduce($publicRoutes, function ($carry, $route) use ($uri) {
    return $carry || strpos($uri, $route) === 0;
}, false);

// Redirigir al login si el usuario no está autenticado
if (!isLoggedIn() && !$isPublicRoute) {
    header('Location: ' . $basePath . '/login');
    exit;
}

// *** API ***
if (preg_match('#^/api/concursos/([0-9\-kK]+)$#', $uri, $matches)) {
    $rut = $matches[1];
    (new ApiController())->obtenerConcursosPorRut($rut);
    exit;
}

// *** Autenticación ***
if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    login($_POST['username'], $_POST['password']);
    exit;
} elseif ($uri === '/login') {
    require __DIR__ . '/../app/views/login.php';
    exit;
}

// *** Rutas de sorteos ***
if ($uri === '/sorteos') {
    (new SorteosController())->index();
    exit;
}

if ($uri === '/sorteos/crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new SorteosController())->crearSorteo();
    exit;
}

if (preg_match('#^/sorteos/editar/(\d+)$#', $uri, $matches)) {
    (new SorteosController())->editarSorteo($matches[1]);
    exit;
}

// Redirección desde /sorteos/editar con GET y sorteo_id en la URL
if ($uri === '/sorteos/editar' && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sorteo_id'])) {
    header('Location: /sorteos/editar/' . $_GET['sorteo_id']);
    exit;
}

// *** Funcionalidades de sorteos ***
if ($uri === '/sorteos/cargar-participantes') {
    (new ParticipantesController())->cargarParticipantes();
    exit;
}

if ($uri === '/sorteos/anular-ganador' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new SorteosController())->anularGanador();
    exit;
}

// *** Administración de participantes ***
if ($uri === "$basePath/participantes/banear" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new ParticipantesController())->banearParticipante();
    exit;
}

if ($uri === "$basePath/participantes/desbanear" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new ParticipantesController())->desbanearParticipante();
    exit;
}

// *** Otras funcionalidades del sorteo ***
if ($uri === '/sorteos/realizar-sorteo') {
    (new SorteosController())->realizarSorteo();
    exit;
}

if ($uri === '/sorteos/publicar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new SorteosController())->publicar();
    exit;
}

if ($uri === '/sorteos/despublicar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new SorteosController())->despublicar();
    exit;
}

// *** Vista pública de resultados ***
if (preg_match('#^/sorteos/resultado/(\d+)$#', $uri, $matches)) {
    (new SorteosController())->verResultado($matches[1]);
    exit;
}

// Ruta para verificar si un sorteo existe
if (preg_match('/^\/sorteos\/verificar\/(\d+)$/', $uri, $matches)) {
    $sorteo_id = $matches[1];
    header('Content-Type: application/json');
    
    // Verificar si el sorteo existe
    $sorteo = Sorteo::obtenerPorId($sorteo_id);
    echo json_encode(['exists' => $sorteo ? true : false]);
    exit;
}

// *** Cerrar sesión ***
if ($uri === '/logout') {
    logout();
    exit;
}

// *** Manejo de rutas no válidas ***
header("HTTP/1.0 404 Not Found");
require __DIR__ . '/../app/views/404.php';
exit;

