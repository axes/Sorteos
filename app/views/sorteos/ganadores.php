<?php
$title = 'Ganadores del Sorteo';
ob_start();
?>

<h1>Ganadores del Sorteo: <?= htmlspecialchars($sorteo['nombre']); ?></h1>

<?php if (isset($ganadores['al_agua'])): ?>
    <h2>Al Agua:</h2>
    <ul>
        <?php foreach ($ganadores['al_agua'] as $al_agua): ?>
            <li><?= htmlspecialchars($al_agua['nombre']); ?> - <?= htmlspecialchars($al_agua['email']); ?></li>
        <?php endforeach; ?>
    </ul>
    <h2>Ganador:</h2>
    <ul>
        <?php foreach ($ganadores['ganador'] as $ganador): ?>
            <li><?= htmlspecialchars($ganador['nombre']); ?> - <?= htmlspecialchars($ganador['email']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <h2>Ganador(es):</h2>
    <ul>
        <?php foreach ($ganadores as $ganador): ?>
            <li><?= htmlspecialchars($ganador['nombre']); ?> - <?= htmlspecialchars($ganador['email']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<a href="/sorteos/editar/<?= $sorteo_id; ?>" class="btn btn-primary mt-3">Volver al Sorteo</a>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
