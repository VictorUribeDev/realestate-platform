<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
}
?>