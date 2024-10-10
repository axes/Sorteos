<?php if (isset($resultados['al_agua'])): ?>
    <h3>Al Agua:</h3>
    <ul>
        <?php foreach ($resultados['al_agua'] as $al_agua): ?>
            <li><?= htmlspecialchars($al_agua['nombre']); ?> - <?= htmlspecialchars($al_agua['email']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h3>Ganador(es):</h3>
<ul class="list-group bordesombreado rainbow my-3 py-3">
    <?php foreach ($resultados['ganadores'] as $ganador): ?>
        <li class="list-group-item ">
            <?= htmlspecialchars($ganador['nombre']); ?> - <?= htmlspecialchars($ganador['email']); ?>
            <?php if (isset($ganador['lugar'])): ?>
                (<?= $ganador['lugar']; ?>º Lugar)
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
