<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = "Error de seguridad CSRF";
    header('Location: mensajes.php');
    exit;
}

$id_mensaje = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id_mensaje) {
    $_SESSION['error'] = "ID de mensaje inválido";
    header('Location: mensajes.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM mensajes WHERE id = ?");
    $stmt->execute([$id_mensaje]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Mensaje eliminado correctamente";
    } else {
        $_SESSION['error'] = "El mensaje no existe";
    }
    
    header('Location: mensajes.php');
    exit;

} catch (PDOException $e) {
    error_log("Error eliminando mensaje: " . $e->getMessage());
    $_SESSION['error'] = "Error al eliminar el mensaje";
    header('Location: mensajes.php');
    exit;
}