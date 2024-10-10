<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// //ruta de routes.php
require_once '../config/routes.php';


// Configuración horario America Santiago
date_default_timezone_set('America/Santiago');