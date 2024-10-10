<?php
$title = 'Editar Sorteo - ' . htmlspecialchars($sorteo['nombre']);
ob_start();
?>

<?php
function enmascararEmail($email) {
    $partes = explode('@', $email);
    return substr($partes[0], 0, 2) . '***@' . $partes[1];
}
?>

<?php
function enmascararRut($rut) {
    return substr($rut, 0, 4) . '*****';
}
?>

<div class="row justify-content-center">
    <div class="col-8 text-center mb-3">
        <small>Editar Sorteo:</small>
        <h1> <?= htmlspecialchars($sorteo['nombre']); ?></h1>
    </div>

    <?php if ($sorteo['cerrado']): ?>
        <div class="col-8 text-center mb-3">
            <div class="alert alert-warning">
                Este sorteo está cerrado. <br /> No puedes agregar participantes ni realizar el sorteo.
            </div>
            <p>Fecha y hora del sorteo: <?= date('d-m-Y H:i', strtotime($sorteo['fecha_cierre'])); ?></p>
            <?php
            // Obtener los ganadores, ganadores anulados y "al agua"
            $ganadores = Sorteo::obtenerGanadores($sorteo_id);
            $ganadoresAnulados = Sorteo::obtenerGanadoresAnulados($sorteo_id);
            $al_agua = Sorteo::obtenerAlAgua($sorteo_id);
            ?>
        </div>

        <div class="col-8 text-center mb-3">
            <?php if ($ganadores): ?>
                <div class="rainbow  bordesombreado py-5 px-3 mb-5">
                    <h2 class="mb-3">Ganador(es):</h2>
                    <ul class="list-group">
                        <?php foreach ($ganadores as $ganador): ?>
                            <li class="list-group-item">
                                [<?= $ganador['lugar']; ?>º] <?= htmlspecialchars($ganador['nombre']); ?> - <?= htmlspecialchars(enmascararEmail($ganador['email'])); ?> - <?= htmlspecialchars(enmascararRut($ganador['rut'])); ?>
                                <!-- Botón para anular ganador -->
                                <button class="btn btn-outline-danger btn-sm anular-ganador ms-3" data-participante-id="<?= $ganador['id']; ?>">
                                    Anular Ganador
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p>No hay ganadores registrados.</p>
            <?php endif; ?>

            <?php if ($ganadoresAnulados): ?>
                <div class="bordesombreado mb-3">
                    <h3>Ganadores Anulados:</h3>
                    <ul class="list-unstyled">
                        <?php foreach ($ganadoresAnulados as $anulado): ?>
                            <li>
                                [<?= $anulado['lugar']; ?>º] <?= htmlspecialchars($anulado['nombre']); ?> - <?= htmlspecialchars($anulado['email']); ?>
                                <span class="text-danger">(Anulado)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($al_agua): ?>
                <h3>Al Agua:</h3>
                <ul>
                    <?php foreach ($al_agua as $participante): ?>
                        <li><?= htmlspecialchars($participante['nombre']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($sorteo['publicado']): ?>
                <p>Puedes ver el resultado público en: <a href="/sorteos/resultado/<?= $sorteo_id; ?>">/sorteos/resultado/<?= $sorteo_id; ?></a></p>
            <?php endif; ?>

            <?php if ($sorteo['publicado']): ?>
                <button class="btn btn-warning my-2" id="despublicarSorteo">Despublicar Resultado</button>
            <?php else: ?>
                <button class="btn btn-success my-2" id="publicarSorteo">Publicar Resultado</button>
            <?php endif; ?>

            <button class="btn btn-primary my-5" onclick="window.location.href='/sorteos'">Volver a la lista de sorteos</button>
        </div>


    <?php else: ?>
        <!-- Formulario para cargar participantes -->
        <div class="col-8 bordesombreado mb-5">
            <h2>Cargar Participantes</h2>
            <form id="cargarParticipantesForm" action="/sorteos/cargar-participantes" method="post" enctype="multipart/form-data">
                <input type="hidden" name="sorteo_id" value="<?= $sorteo_id; ?>">
                <div class="mb-3">
                    <label for="archivo" class="form-label">Archivo CSV:</label>
                    <input type="file" name="archivo" id="archivo" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Cargar Participantes</button>
            </form>
        </div>

        <!-- Mostrar participantes cargados -->
        <div class="col-8 bordesombreado mb-5">
            <h2>Participantes <span id="totalParticipantes">(<?= count($participantes); ?>)</span></h2>
            <div id="listaParticipantes">
                <?php if ($participantes): ?>
                    <ul class="list-group">
                        <?php foreach ($participantes as $participante): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($participante['nombre']); ?> - <?= htmlspecialchars($participante['rut']); ?>
                                <?php if (!$participante['baneado']): ?>
                                    <button class="btn btn-warning btn-sm banear-participante" data-participante-id="<?= $participante['id']; ?>">
                                        Banear
                                    </button>
                                <?php else: ?>
                                    <span class="text-danger">(Baneado)</span>
                                    <button class="btn btn-secondary btn-sm desbanear-participante" data-participante-id="<?= $participante['id']; ?>">
                                        Desbanear
                                    </button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No hay participantes cargados.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario para realizar el sorteo -->
        <section id="realizarSorteo" class="col-8 bordesombreado mb-5 borde-rainbow" style="display: <?= count($participantes) > 0 ? 'block' : 'none'; ?>;">
            <h2 class="mt-4">Realizar Sorteo</h2>
            <form id="realizarSorteoForm" action="/sorteos/realizar-sorteo" method="post">
                <input type="hidden" name="sorteo_id" value="<?= $sorteo_id; ?>">
                <div class="mb-3">
                    <label for="tipo_sorteo" class="form-label">Tipo de Sorteo:</label>
                    <select name="tipo_sorteo" id="tipo_sorteo" class="form-select">
                        <option value="1 ganador">1 Ganador</option>
                        <option value="2 ganadores">2 Ganadores</option>
                        <option value="3 ganadores">3 Ganadores</option>
                        <option value="2 al agua, 1 ganador">2 "al agua" y 1 Ganador</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary button-85 my-3">Realizar Sorteo</button>
            </form>
        </section>

    <?php endif; ?>
</div>


<script>
    const sorteoId = <?= $sorteo_id; ?>;
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>