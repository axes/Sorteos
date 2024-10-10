<?php
$title = 'Resultado del Sorteo - ' . htmlspecialchars($sorteo['nombre']);
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-8 text-center mb-3">
        <h1><?= htmlspecialchars($sorteo['nombre']); ?></h1>
        <p>(Este sorteo está cerrado)</p>
        <p>Fecha y hora del sorteo: <?= date('d-m-Y H:i', strtotime($sorteo['fecha_cierre'])); ?></p>
    </div>

    <div class="col-8 text-center mb-3">
        <?php if ($ganadores): ?>
            <div class="rainbow  bordesombreado py-5 px-3 mb-5">
                <h2 class="mb-3">Ganador(es):</h2>
                <ul class="list-group">
                    <?php foreach ($ganadores as $ganador): ?>
                        <li class="list-group-item">
                            [<?= $ganador['lugar']; ?>º] <?= htmlspecialchars($ganador['nombre']); ?> - <?= htmlspecialchars($ganador['email']); ?>
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
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout_public.php';
?>