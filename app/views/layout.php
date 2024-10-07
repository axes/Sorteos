<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema de Sorteos'; ?></title>
    <link rel="icon" type="image/png" href="/sorteos/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/sorteos/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container justify-content-center">
            <a class="navbar-brand" href="/sorteos">
                <img src="/sorteos/img/logo.png" alt="Logo" height="64" class="d-inline-block align-text-middle">
            </a>
            <?php if (isset($_SESSION['user'])): ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item " data-bs-theme="dark">
                        <a type="button" class="btn-close nav-link text-white" href="/sorteos/logout" aria-label="Close"></a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Contenido de cada página -->
        <?= $content ?? ''; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- App JS -->
    <script src="/sorteos/js/app.js"></script>
</body>

</html>