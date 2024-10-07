<?php
$title = 'Resultado no disponible';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-8 text-center mb-3">
        <h1>Resultado no disponible</h1>
        <p>El resultado de este sorteo no está disponible actualmente.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout_public.php';
?>
