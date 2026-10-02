<?php
session_start();
require_once '../includes/database.php';

// ==================== CONFIGURACIONES DE SEGURIDAD ====================
// Verificar HTTPS y entorno local
$is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
           (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (!$is_https && !$is_local) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Configuración de seguridad
define('REGISTER_KEY', 'CFR100!c10RSG550'); // Cambiar en producción
define('MAX_ATTEMPTS', 5); // Intentos máximos permitidos
define('LOCKOUT_TIME', 300); // 5 minutos en segundos

// ==================== PROTECCIÓN CONTRA ATAQUES ====================
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// ==================== MANEJO DE INTENTOS ====================
if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = [
        'count' => 0,
        'last_attempt' => 0
    ];
}

// Bloquear por demasiados intentos
if ($_SESSION['register_attempts']['count'] >= MAX_ATTEMPTS) {
    $remaining_time = LOCKOUT_TIME - (time() - $_SESSION['register_attempts']['last_attempt']);
    if ($remaining_time > 0) {
        die("Demasiados intentos. Por favor intente en " . ceil($remaining_time/60) . " minutos.");
    } else {
        $_SESSION['register_attempts'] = [
            'count' => 0,
            'last_attempt' => 0
        ];
    }
}

// ==================== TOKEN CSRF ====================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==================== LOGICA PRINCIPAL ====================
// Redirigir si ya está autenticado
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$nombre = '';
$email = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Error de seguridad. Recargue la página e intente nuevamente.');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        $register_key = trim($_POST['register_key'] ?? '');

        // Validaciones
        $error = validateRegistration($nombre, $email, $password, $confirm_password, $register_key);
        
        if (!empty($error)) {
            throw new Exception($error);
        }

        // Verificar email existente
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('El email ya está registrado');
        }

        // Crear hash de contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $hash]);
        
        $success = 'Administrador registrado exitosamente! <a href="login.php">Iniciar Sesión</a>';
        $nombre = '';
        $email = '';
        logAttempt(true);

    } catch (PDOException $e) {
        error_log("Error de registro: " . $e->getMessage());
        $error = 'Error interno. Contacte al soporte técnico.';
        logAttempt(false);
    } catch (Exception $e) {
        $error = $e->getMessage();
        logAttempt(false);
    }
}

// ==================== FUNCIONES ====================
function validateRegistration($nombre, $email, $password, $confirm_password, $register_key) {
    $errors = [];
    
    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password) || empty($register_key)) {
        $errors[] = 'Todos los campos son obligatorios';
    }
    
    // Validar nombre
    if (strlen($nombre) > 100) {
        $errors[] = 'El nombre no puede exceder los 100 caracteres';
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Formato de email inválido';
    } elseif (strlen($email) > 100) {
        $errors[] = 'El email no puede exceder los 100 caracteres';
    }
    
    // Validar contraseña
    if (strlen($password) < 12) {
        $errors[] = 'La contraseña debe tener al menos 12 caracteres';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Debe contener al menos una mayúscula';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Debe contener al menos un número';
    }
    if (!preg_match('/[\W]/', $password)) {
        $errors[] = 'Debe contener al menos un símbolo especial';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Las contraseñas no coinciden';
    }
    
    // Validar clave de registro
    if ($register_key !== REGISTER_KEY) {
        $errors[] = 'Clave de registro inválida';
    }
    
    return implode('<br>', $errors);
}

function logAttempt($success) {
    if (!$success) {
        $_SESSION['register_attempts']['count']++;
        $_SESSION['register_attempts']['last_attempt'] = time();
    } else {
        $_SESSION['register_attempts'] = [
            'count' => 0,
            'last_attempt' => 0
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Admin - Flies Propiedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-container {
            max-width: 500px;
            margin: 5rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .password-rules {
            font-size: 0.9em;
            color: #666;
        }
        .alert ul {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4">Registro de Administrador</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php else: ?>
            
            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($nombre) ?>" required
                           maxlength="100" autocomplete="name">
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= htmlspecialchars($email) ?>" required
                           maxlength="100" autocomplete="email">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           required minlength="12" autocomplete="new-password">
                    <div class="password-rules">
                        <small>Requisitos mínimos:</small>
                        <ul class="mt-1">
                            <li>12 caracteres mínimo</li>
                            <li>1 letra mayúscula</li>
                            <li>1 número</li>
                            <li>1 símbolo especial</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           required autocomplete="new-password">
                </div>
                
                <div class="mb-3">
                    <label for="register_key" class="form-label">Clave de Registro</label>
                    <input type="password" class="form-control" id="register_key" name="register_key" 
                           required autocomplete="off">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Registrar Administrador</button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>