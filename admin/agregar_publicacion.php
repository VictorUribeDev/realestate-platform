<?php
ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '264M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '512M');
session_start();
require_once '../includes/database.php';

// Verificar autenticación PRIMERO
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Generar token CSRF SIEMPRE --------> CORRECCIÓN 1
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar existencia de tokens --------> CORRECCIÓN 2
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        die('Token CSRF no proporcionado');
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Token CSRF inválido');
    }


    // Recibir datos del formulario
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $operacion = $_POST['operacion'] ?? '';
    $estado = $_POST['estado'] ?? 'disponible';
    $precio = $_POST['precio'] ?? null;
    $moneda = $_POST['moneda'] ?? '';
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $mapa_url = trim($_POST['mapa_url'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $num_banos = $_POST['num_banos'] ?? null;
    $num_dormitorios = $_POST['num_dormitorios'] ?? null;
    $superficie_construida = $_POST['superficie_construida'] ?? null;
    $superficie_total = $_POST['superficie_total'] ?? null;
    $video = trim($_POST['video'] ?? '');
    $usuario_id = $_SESSION['user_id'];
    
    // Manejo de archivos
    $imagen_principal = null;
    $imagenes_adicionales = [];

    // Procesar imagen principal
    if (!empty($_FILES['imagen_principal']['name'])) {
        $upload_dir = '../public/images/propiedades/';
        $nombre_original = basename($_FILES['imagen_principal']['name']);
        $nuevo_nombre = time() . "_principal_" . $nombre_original;
        $ruta_destino = $upload_dir . $nuevo_nombre;

        if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $ruta_destino)) {
            $imagen_principal = $nuevo_nombre;
        }
    }
// Manejo del video --------> CORRECCIÓN 3
$video = null;
if (!empty($_FILES['video']['name'])) {
    $upload_dir = '../public/videos/propiedades/';
    $nombre_original = basename($_FILES['video']['name']);
    $nuevo_nombre = time() . "_video_" . $nombre_original;
    $ruta_destino = $upload_dir . $nuevo_nombre;
    
    if (move_uploaded_file($_FILES['video']['tmp_name'], $ruta_destino)) {
        $video = $nuevo_nombre;
    }
}
    // Validación básica
    $campos_requeridos = [
        'Título' => $titulo,
        'Tipo de propiedad' => $tipo,
        'Tipo de operación' => $operacion,
        'Precio' => $precio,
        'Moneda' => $moneda,
        'Ubicación' => $ubicacion
    ];

    $errores = [];
    foreach ($campos_requeridos as $campo => $valor) {
        if (empty($valor)) {
            $errores[] = "El campo $campo es requerido";
        }
    }

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            // Insertar publicación principal
            $stmt = $pdo->prepare("INSERT INTO publicaciones 
                    (titulo, tipo, operacion, estado, precio, moneda, ubicacion, mapa_url,
                    descripcion, imagen_principal, video, usuario_id, num_banos, 
                    num_dormitorios, superficie_construida, superficie_total) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); 
            
            $stmt->execute([
                $titulo, $tipo, $operacion, $estado, $precio, $moneda, $ubicacion, $mapa_url, $descripcion,
                $imagen_principal, $video, $usuario_id, $num_banos, $num_dormitorios, 
                $superficie_construida, $superficie_total
            ]);

            $publicacion_id = $pdo->lastInsertId();

            // Procesar imágenes adicionales
            if (!empty($_FILES['imagenes_adicionales']['name'][0])) {
                $upload_dir = '../public/images/propiedades/adicionales/';
                $contador = 1;
                
                foreach ($_FILES['imagenes_adicionales']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagenes_adicionales']['error'][$key] === UPLOAD_ERR_OK) {
                        $nombre_original = basename($_FILES['imagenes_adicionales']['name'][$key]);
                        $nuevo_nombre = time() . "_additional_" . $key . "_" . $nombre_original;
                        $ruta_destino = $upload_dir . $nuevo_nombre;

                        if (move_uploaded_file($tmp_name, $ruta_destino)) {
                            $stmt_img = $pdo->prepare("INSERT INTO imagenes 
                                                      (publicacion_id, imagen_url, orden) 
                                                      VALUES (?, ?, ?)");
                            $stmt_img->execute([$publicacion_id, $nuevo_nombre, $contador]);
                            $contador++;
                        }
                    }
                }
            }

            $pdo->commit();
            header('Location: dashboard.php?mensaje=Propiedad creada correctamente');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al guardar la propiedad: " . $e->getMessage();
        }
    } else {
        $error = implode("<br>", $errores);
    }
}

require_once '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h2>Nueva Propiedad</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label>Título</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            
            <div class="col-md-6">
                <label>Tipo de Propiedad</label>
                <select name="tipo" class="form-select" required>
                    <option value="casa">Casa</option>
                    <option value="departamento">Departamento</option>
                    <option value="parcela">Parcela</option>
                    <option value="oficina">Oficina</option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label>Tipo de Operación</label>
                <select name="operacion" class="form-select" required>
                    <option value="venta">Venta</option>
                    <option value="arriendo">Arriendo</option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label>Estado</label>
                <select name="estado" class="form-select" required>
                    <option value="disponible">Disponible</option>
                    <option value="vendida">Vendida</option>
                    <option value="arrendada">Arrendada</option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label>Precio</label>
                <input type="number" name="precio" class="form-control" step="0.01" required>
            </div>
            
            <div class="col-md-6">
                <label>Moneda</label>
                <input type="text" name="moneda" class="form-control" required>
            </div>
            
            <div class="col-md-12">
                <label>Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label>Enlace de Google Maps (Embed)</label>
                    <input type="url" name="mapa_url" class="form-control" 
                    placeholder="https://www.google.com/maps/embed?pb=...">
                <small class="text-muted">
                    <a href="https://support.google.com/maps/answer/144361?hl=es" target="_blank">
                    ¿Cómo obtener el enlace de inserción?
                    </a>
                </small>
            </div>
            
            <div class="col-md-12">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4"></textarea>
            </div>
            
            <div class="col-md-6">
                <label>Baños</label>
                <input type="number" name="num_banos" class="form-control" min="0">
            </div>
            
            <div class="col-md-6">
                <label>Dormitorios</label>
                <input type="number" name="num_dormitorios" class="form-control" min="0">
            </div>
            
            <div class="col-md-6">
                <label>Superficie Construida (m²)</label>
                <input type="number" name="superficie_construida" class="form-control" step="0.01">
            </div>
            
            <div class="col-md-6">
                <label>Superficie Total (m²)</label>
                <input type="number" name="superficie_total" class="form-control" step="0.01">
            </div>

            
            <!-- Sección de Video -->
            <div class="col-md-12">
                <label>Video (Formatos permitidos: MP4, MOV, AVI, MKV)</label>
                <input type="file" name="video" class="form-control" accept="video/*">
            </div>

            <!-- Sección de Imágenes -->
            <div class="col-md-12">
                <label>Imagen Principal</label>
                <input type="file" name="imagen_principal" class="form-control" accept="image/*">
            </div>

            <div class="col-md-12">
                <label>Imágenes Adicionales (Máximo 10)</label>
                <input type="file" name="imagenes_adicionales[]" class="form-control" 
                       accept="image/*" multiple>
                <small class="text-muted">Formatos permitidos: JPG, PNG, GIF</small>
            </div>
        </div>

       

        <button type="submit" class="btn btn-primary mt-3">Guardar Propiedad</button>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>