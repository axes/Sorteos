<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function login($username, $password) {
    global $basePath;
    // Autenticación de usuario simple
    // TODO: Implementar autenticación segura
    if ($username === 'admin' && $password === 'password') {
        $_SESSION['user'] = $username;
        header('Location: ' . $basePath . '/sorteos');
        exit;
    } else {
        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ' . $basePath . '/login');
        exit;
    }
}

function logout() {
    global $basePath;
    session_destroy();
    header('Location: ' . $basePath . '/login');
}