<?php
 // Solo una vez al inicio
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'contacto.php');
    exit;
}

// Validar CSRF (agrega esto en tu formulario)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Error de seguridad. Intente nuevamente.";
    header('Location: ' . BASE_URL . 'contacto.php');
    exit;
}

// Validar campos
$required = ['nombre', 'email', 'mensaje'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "El campo $field es requerido";
        header('Location: ' . BASE_URL . 'contacto.php');
        exit;
    }
}

// Sanitizar
$nombre = htmlspecialchars($_POST['nombre']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$telefono = $_POST['telefono'] ?? '';
$mensaje = htmlspecialchars($_POST['mensaje']);

try {
    if (!$pdo) {
        throw new PDOException("Error: Revisa la conexión a la base de datos");
    }

    $stmt = $pdo->prepare("INSERT INTO mensajes 
        (nombre, email, telefono, mensaje, fecha) 
        VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$nombre, $email, $telefono, $mensaje]);
    
    $_SESSION['success'] = "Mensaje enviado correctamente";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al enviar el mensaje: " . $e->getMessage();
}

header('Location: ' . BASE_URL . 'contacto.php');
exit;