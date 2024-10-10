<?php
$title = 'Gestión de Sorteos';

ob_start(); // Inicia el buffer de salida
?>

<div class="row justify-content-center">
    <h1 class="text-center mb-4">Gestión de Sorteos</h1>

    <!-- Primera etapa: Selección entre crear o editar un sorteo -->
    <section id="paso1" class="mb-4 bordesombreado col-8">
        <h3 class="mb-4">¿Qué deseas hacer?</h3>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="sorteoOption" id="crearNuevo" value="crear" onchange="mostrarSiguientePaso()">
            <label class="form-check-label" for="crearNuevo"> Crear nuevo sorteo </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="sorteoOption" id="editarExistente" value="editar" onchange="mostrarSiguientePaso()">
            <label class="form-check-label" for="editarExistente"> Editar un sorteo existente </label>
        </div>
    </section>

    <!-- Segunda etapa: Crear nuevo sorteo (se oculta inicialmente) -->
    <section id="crearSorteo" style="display: none;" class="mb-4 bordesombreado col-8">
        <h3>Crear nuevo sorteo</h3>
        <form id="crearSorteoForm" action="/sorteos/crear" method="post">
            <div class="mb-3">
                <label for="nombre_sorteo" class="form-label">Nombre del Sorteo:</label>
                <input type="text" name="nombre" id="nombre_sorteo" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Crear Sorteo</button>
        </form>
    </section>

    <!-- Segunda etapa: Seleccionar un sorteo existente (se oculta inicialmente) -->
    <div id="editarSorteo" style="display: none;" class="mb-4 bordesombreado col-8">
        <h3>Seleccionar un sorteo existente</h3>
        <form id="editarSorteoForm" action="/sorteos/editar" method="get">
            <div class="mb-3">
                <label for="sorteo_id" class="form-label">Selecciona un Sorteo:</label>
                <select name="sorteo_id" id="sorteo_id" class="form-select">
                    <?php foreach ($sorteos as $sorteo): ?>
                        <option value="<?= $sorteo['id']; ?>"><?= htmlspecialchars($sorteo['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Editar Sorteo</button>
        </form>
    </div>
    
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
