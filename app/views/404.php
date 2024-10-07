<?php
$title = 'Página no encontrada';

ob_start(); // Inicia el buffer de salida
?>

<div class="text-center">
    <h1 class="display-4">404 - Página no encontrada</h1>
    <p>Lo sentimos, la página que estás buscando no existe.</p>
    <a href="/sorteos/sorteos" class="btn btn-primary">Volver a la página principal</a>
</div>

<?php
$content = ob_get_clean(); // Capturar el contenido generado
require __DIR__ . '/layout.php'; // Usa el layout general
