<?php
$title = 'Iniciar Sesión';

ob_start(); // Iniciar el buffer de salida
?>

<section id="login" class="row justify-content-center">
    <div class="col-md-4 bordesombreado">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>

        <!-- Mostrar error si existe -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); // Limpiar el error después de mostrarlo 
            ?>
        <?php endif; ?>

        <form action="/sorteos/login" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario:</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</section>

<?php
$content = ob_get_clean(); // Capturar el contenido generado
require __DIR__ . '/layout.php'; // Cargar el layout
