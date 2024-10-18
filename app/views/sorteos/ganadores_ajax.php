<?php
function enmascararEmail($email) {
    if (strpos($email, '@') !== false) {
        $partes = explode('@', $email);
        return substr($partes[0], 0, 2) . '***@' . $partes[1];
    } else {
        // Manejo de error si el email no tiene formato válido
        return 'Email inválido';
    }
}

function enmascararRut($rut) {
    return substr($rut, 0, 4) . '*****';
}
?>


<?php if (isset($resultados['al_agua'])): ?>
    <h3>Al Agua:</h3>
    <ul class="list-unstyled">
        <?php foreach ($resultados['al_agua'] as $al_agua): ?>
            <li><?= htmlspecialchars($al_agua['nombre']); ?> - <?= htmlspecialchars(enmascararEmail($al_agua['email'])); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h3>Ganador(es):</h3>
<ul class="list-group bordesombreado rainbow my-3 py-3">
    <?php foreach ($resultados['ganadores'] as $ganador): ?>
        <li class="list-group-item ">
            <?= htmlspecialchars($ganador['nombre']); ?> - <?= htmlspecialchars(enmascararEmail($ganador['email'])); ?> - <?= htmlspecialchars(enmascararRut($ganador['rut'])); ?>
            <?php if (isset($ganador['lugar'])): ?>
                (<?= $ganador['lugar']; ?>º Lugar)
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
