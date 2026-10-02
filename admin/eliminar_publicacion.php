<?php
session_start();
require_once '../includes/database.php';

// Verificar autenticación y rol de admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = "Error de seguridad CSRF";
    header('Location: dashboard.php');
    exit;
}

// Validar ID
$id_publicacion = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id_publicacion) {
    $_SESSION['error'] = "ID de publicación inválido";
    header('Location: dashboard.php');
    exit;
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Obtener información de archivos adjuntos
    $stmt = $pdo->prepare("SELECT imagen_principal, video FROM publicaciones WHERE id = ?");
    $stmt->execute([$id_publicacion]);
    $archivos = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Eliminar imágenes adicionales
    $stmt = $pdo->prepare("SELECT imagen_url FROM imagenes WHERE publicacion_id = ?");
    $stmt->execute([$id_publicacion]);
    $imagenes_adicionales = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Eliminar registros de imágenes adicionales
    $stmt = $pdo->prepare("DELETE FROM imagenes WHERE publicacion_id = ?");
    $stmt->execute([$id_publicacion]);

    // 3. Eliminar publicación principal
    $stmt = $pdo->prepare("DELETE FROM publicaciones WHERE id = ?");
    $stmt->execute([$id_publicacion]);

    // Commit de la transacción
    $pdo->commit();

    // Eliminar archivos físicos después de éxito en la transacción
    // Función para eliminar archivos de forma segura
    $eliminarArchivo = function($rutaBase, $nombreArchivo) {
        if (!empty($nombreArchivo)) {
            $nombreSeguro = basename($nombreArchivo); // Prevenir path traversal
            $rutaCompleta = $rutaBase . $nombreSeguro;
            if (file_exists($rutaCompleta)) {
                @unlink($rutaCompleta); // Suprimir errores
            }
        }
    };

    // Eliminar imagen principal
    $eliminarArchivo('../public/images/propiedades/', $archivos['imagen_principal'] ?? '');

    // Eliminar video
    $eliminarArchivo('../public/videos/propiedades/', $archivos['video'] ?? '');

    // Eliminar imágenes adicionales
    if (!empty($imagenes_adicionales)) {
        foreach ($imagenes_adicionales as $imagen) {
            $eliminarArchivo('../public/images/propiedades/adicionales/', $imagen);
        }
    }

    $_SESSION['success'] = "Publicación eliminada exitosamente";
    header('Location: dashboard.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("[" . date('Y-m-d H:i:s') . "] Error eliminando publicación: " . $e->getMessage() . "\n", 3, "../admin/error.log");
    $_SESSION['error'] = "Error al eliminar la publicación: " . $e->getMessage();
    header("Location: editar_publicacion.php?id=$id_publicacion");
    exit;
}