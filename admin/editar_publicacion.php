<?php
session_start();
require_once '../includes/database.php';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Generar CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Obtener ID de publicación
$id_publicacion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_publicacion) {
    $_SESSION['error'] = "ID de publicación inválido";
    header('Location: dashboard.php');
    exit;
}

// Obtener datos de la publicación
try {
    $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE id = ?");
    $stmt->execute([$id_publicacion]);
    $publicacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$publicacion) {
        $_SESSION['error'] = "Publicación no encontrada";
        header('Location: dashboard.php');
        exit;
    }
    
    // Obtener imágenes adicionales
    $stmtImagenes = $pdo->prepare("SELECT * FROM imagenes WHERE publicacion_id = ? ORDER BY orden");
    $stmtImagenes->execute([$id_publicacion]);
    $imagenes_adicionales = $stmtImagenes->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error obteniendo datos: " . $e->getMessage());
    $_SESSION['error'] = "Error al cargar la publicación";
    header('Location: dashboard.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Error de seguridad CSRF";
        header("Location: editar_publicacion.php?id=$id_publicacion");
        exit;
    }

    // Recoger y sanitizar datos
    $datos = [
        'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING),
        'descripcion' => filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING),
        'tipo' => filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING),
        'operacion' => filter_input(INPUT_POST, 'operacion', FILTER_SANITIZE_STRING),
        'estado' => filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING),
        'precio' => filter_input(INPUT_POST, 'precio', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'moneda' => filter_input(INPUT_POST, 'moneda', FILTER_SANITIZE_STRING),
        'num_dormitorios' => filter_input(INPUT_POST, 'num_dormitorios', FILTER_VALIDATE_INT),
        'num_banos' => filter_input(INPUT_POST, 'num_banos', FILTER_VALIDATE_INT),
        'superficie_construida' => filter_input(INPUT_POST, 'superficie_construida', FILTER_VALIDATE_INT),
        'superficie_total' => filter_input(INPUT_POST, 'superficie_total', FILTER_VALIDATE_INT),
        'ubicacion' => filter_input(INPUT_POST, 'ubicacion', FILTER_SANITIZE_STRING)
    ];

    // Validar datos
    $errores = [];
    foreach ($datos as $campo => $valor) {
        if (empty($valor) && $valor !== 0) {
            $errores[] = "El campo " . ucfirst(str_replace('_', ' ', $campo)) . " es requerido";
        }
    }

    // Manejar imagen principal
    $imagen_principal = $publicacion['imagen_principal'];
    $eliminar_imagen = isset($_POST['eliminar_imagen']);

    if (!empty($_FILES['imagen_principal']['name'])) {
        $uploads_dir = '../public/images/propiedades';
        $nombre_archivo = $_FILES['imagen_principal']['name'];
        $tamano_archivo = $_FILES['imagen_principal']['size'];
        $tmp_name = $_FILES['imagen_principal']['tmp_name'];
        
        // Validar imagen
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if ($tamano_archivo > 5 * 1024 * 1024) {
            $errores[] = "La imagen excede el tamaño máximo (5MB)";
        }
        
        if (!in_array($extension, $extensiones_permitidas)) {
            $errores[] = "Formato de imagen no permitido";
        }
        
        // Generar nombre único
        if (empty($errores)) {
            $nombre_unico = uniqid('img_') . '_' . preg_replace('/[^A-Za-z0-9_\-.]/', '_', $nombre_archivo);
            $destino = "$uploads_dir/$nombre_unico";
            
            if (move_uploaded_file($tmp_name, $destino)) {
                // Eliminar imagen anterior
                if (!empty($imagen_principal)) {
                    $ruta_anterior = "$uploads_dir/$imagen_principal";
                    if (file_exists($ruta_anterior)) unlink($ruta_anterior);
                }
                $imagen_principal = $nombre_unico;
            }
        }
    } elseif ($eliminar_imagen) {
        if (!empty($imagen_principal)) {
            $ruta_imagen = "../public/images/propiedades/$imagen_principal";
            if (file_exists($ruta_imagen)) unlink($ruta_imagen);
            $imagen_principal = null;
        }
    }

    // Manejar video
    $video = $publicacion['video'];
    $eliminar_video = isset($_POST['eliminar_video']);

    if (!empty($_FILES['video']['name'])) {
        $videos_dir = '../public/videos/propiedades';
        $nombre_video = $_FILES['video']['name'];
        $tamano_video = $_FILES['video']['size'];
        $tmp_video = $_FILES['video']['tmp_name'];
        
        // Validar video
        $extension_video = strtolower(pathinfo($nombre_video, PATHINFO_EXTENSION));
        
        if ($tamano_video > 50 * 1024 * 1024) {
            $errores[] = "El video excede 50MB";
        }
        
        if ($extension_video !== 'mp4') {
            $errores[] = "Solo se permiten videos MP4";
        }
        
        // Generar nombre único
        if (empty($errores)) {
            $nombre_unico_video = uniqid('vid_') . '_' . preg_replace('/[^A-Za-z0-9_\-.]/', '_', $nombre_video);
            $destino_video = "$videos_dir/$nombre_unico_video";
            
            if (move_uploaded_file($tmp_video, $destino_video)) {
                // Eliminar video anterior
                if (!empty($video)) {
                    $ruta_anterior = "$videos_dir/$video";
                    if (file_exists($ruta_anterior)) unlink($ruta_anterior);
                }
                $video = $nombre_unico_video;
            }
        }
    } elseif ($eliminar_video) {
        if (!empty($video)) {
            $ruta_video = "../public/videos/propiedades/$video";
            if (file_exists($ruta_video)) unlink($ruta_video);
            $video = null;
        }
    }

    // Manejar eliminación de imágenes adicionales
    if (!empty($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
        try {
            $placeholders = implode(',', array_fill(0, count($_POST['eliminar_imagenes']), '?'));
            $stmtDelete = $pdo->prepare("DELETE FROM imagenes WHERE id IN ($placeholders)");
            $stmtDelete->execute($_POST['eliminar_imagenes']);
            
            // Eliminar archivos
            foreach ($_POST['eliminar_imagenes'] as $idImagen) {
                foreach ($imagenes_adicionales as $img) {
                    if ($img['id'] == $idImagen) {
                        $ruta = "../public/images/propiedades/adicionales/{$img['imagen_url']}";
                        if (file_exists($ruta)) unlink($ruta);
                    }
                }
            }
            
        } catch (PDOException $e) {
            error_log("Error eliminando imágenes: " . $e->getMessage());
            $errores[] = "Error al eliminar imágenes";
        }
    }

    // Subir nuevas imágenes adicionales
    if (!empty($_FILES['imagenes_adicionales']['name'][0])) {
        $uploads_dir = '../public/images/propiedades/adicionales';
        
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }

        try {
            $stmtOrden = $pdo->prepare("SELECT MAX(orden) FROM imagenes WHERE publicacion_id = ?");
            $stmtOrden->execute([$id_publicacion]);
            $orden = (int)$stmtOrden->fetchColumn() + 1;

            foreach ($_FILES['imagenes_adicionales']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['imagenes_adicionales']['error'][$key] !== UPLOAD_ERR_OK) continue;

                $nombre_archivo = $_FILES['imagenes_adicionales']['name'][$key];
                $nombre_sanitizado = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $nombre_archivo);
                $extension = strtolower(pathinfo($nombre_sanitizado, PATHINFO_EXTENSION));

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $errores[] = "Formato no permitido: $nombre_archivo";
                    continue;
                }

                if ($_FILES['imagenes_adicionales']['size'][$key] > 5 * 1024 * 1024) {
                    $errores[] = "Imagen demasiado grande: $nombre_archivo";
                    continue;
                }

                $nombre_unico = uniqid('img_') . "_$nombre_sanitizado";
                $destino = "$uploads_dir/$nombre_unico";

                if (move_uploaded_file($tmp_name, $destino)) {
                    $stmtInsert = $pdo->prepare("INSERT INTO imagenes 
                        (publicacion_id, imagen_url, orden) VALUES (?, ?, ?)");
                    $stmtInsert->execute([$id_publicacion, $nombre_unico, $orden]);
                    $orden++;
                }
            }
            
        } catch (PDOException $e) {
            error_log("Error subiendo imágenes: " . $e->getMessage());
            $errores[] = "Error al subir imágenes";
        }
    }

    // Actualizar publicación si no hay errores
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("UPDATE publicaciones SET
                titulo = ?,
                descripcion = ?,
                tipo = ?,
                operacion = ?,
                estado = ?,
                precio = ?,
                moneda = ?,
                num_dormitorios = ?,
                num_banos = ?,
                superficie_construida = ?,
                superficie_total = ?,
                ubicacion = ?,
                video = ?,
                imagen_principal = ?,
                actualizado_en = NOW()
                WHERE id = ?");
            
            $stmt->execute([
                $datos['titulo'],
                $datos['descripcion'],
                $datos['tipo'],
                $datos['operacion'],
                $datos['estado'],
                $datos['precio'],
                $datos['moneda'],
                $datos['num_dormitorios'],
                $datos['num_banos'],
                $datos['superficie_construida'],
                $datos['superficie_total'],
                $datos['ubicacion'],
                $video,
                $imagen_principal,
                $id_publicacion
            ]);
            
            $_SESSION['success'] = "Publicación actualizada exitosamente";
            header("Location: editar_publicacion.php?id=$id_publicacion");
            exit;
            
        } catch (PDOException $e) {
            error_log("Error actualizando publicación: " . $e->getMessage());
            $_SESSION['error'] = "Error al actualizar la publicación";
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errores);
    }
}

require_once '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4 border-bottom pb-2">Editar Publicación</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="row g-4">
            <!-- Sección izquierda -->
            <div class="col-lg-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-text-paragraph me-2"></i>Información Básica
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($publicacion['titulo']) ?>" required>
                            <div class="invalid-feedback">Por favor ingresa un título</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción <span class="text-danger">*</span></label>
                            <textarea name="descripcion" class="form-control" rows="5" required><?= htmlspecialchars($publicacion['descripcion']) ?></textarea>
                            <div class="invalid-feedback">Por favor escribe una descripción</div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Propiedad <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-select" required>
                                    <?php
                                    $tipos = ['casa', 'departamento', 'parcela', 'oficina'];
                                    foreach ($tipos as $tipo) {
                                        $selected = $publicacion['tipo'] === $tipo ? 'selected' : '';
                                        echo "<option value='$tipo' $selected>" . ucfirst($tipo) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Operación <span class="text-danger">*</span></label>
                                <select name="operacion" class="form-select" required>
                                    <option value="venta" <?= $publicacion['operacion'] === 'venta' ? 'selected' : '' ?>>Venta</option>
                                    <option value="arriendo" <?= $publicacion['operacion'] === 'arriendo' ? 'selected' : '' ?>>Arriendo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-currency-exchange me-2"></i>Precio y Moneda
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select" required>
                                    <?php
                                    $estados = ['disponible', 'vendida', 'arrendada'];
                                    foreach ($estados as $estado) {
                                        $selected = $publicacion['estado'] === $estado ? 'selected' : '';
                                        echo "<option value='$estado' $selected>" . ucfirst($estado) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Moneda <span class="text-danger">*</span></label>
                                <select name="moneda" class="form-select" required>
                                    <option value="CLP" <?= $publicacion['moneda'] === 'CLP' ? 'selected' : '' ?>>CLP</option>
                                    <option value="USD" <?= $publicacion['moneda'] === 'USD' ? 'selected' : '' ?>>USD</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Precio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="precio" class="form-control" 
                                           value="<?= htmlspecialchars($publicacion['precio']) ?>" required>
                                    <div class="invalid-feedback">Ingresa un precio válido</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección derecha -->
            <div class="col-lg-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-building me-2"></i>Detalles de la Propiedad
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Dormitorios <span class="text-danger">*</span></label>
                                <input type="number" name="num_dormitorios" class="form-control" 
                                       value="<?= htmlspecialchars($publicacion['num_dormitorios']) ?>" required>
                                <div class="invalid-feedback">Ingresa número de dormitorios</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Baños <span class="text-danger">*</span></label>
                                <input type="number" name="num_banos" class="form-control" 
                                       value="<?= htmlspecialchars($publicacion['num_banos']) ?>" required>
                                <div class="invalid-feedback">Ingresa número de baños</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Superficie Construida (m²) <span class="text-danger">*</span></label>
                                <input type="number" name="superficie_construida" class="form-control" 
                                       value="<?= htmlspecialchars($publicacion['superficie_construida']) ?>" required>
                                <div class="invalid-feedback">Ingresa superficie construida</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Superficie Total (m²) <span class="text-danger">*</span></label>
                                <input type="number" name="superficie_total" class="form-control" 
                                       value="<?= htmlspecialchars($publicacion['superficie_total']) ?>" required>
                                <div class="invalid-feedback">Ingresa superficie total</div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Ubicación <span class="text-danger">*</span></label>
                                <input type="text" name="ubicacion" class="form-control" 
                                       value="<?= htmlspecialchars($publicacion['ubicacion']) ?>" required>
                                <div class="invalid-feedback">Ingresa una ubicación</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-camera-video me-2"></i>Multimedia
                    </div>
                    <div class="card-body">
                        <!-- Sección de Video -->
                        <div class="mb-4">
                            <label class="form-label">Video (MP4)</label>
                            <?php if (!empty($publicacion['video'])): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="bi bi-file-earmark-play fs-4 text-primary"></i>
                                            <div class="flex-grow-1">
                                                <span class="d-block">Video actual:</span>
                                                <small class="text-muted"><?= $publicacion['video'] ?></small>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="eliminar_video" 
                                                       id="eliminar_video">
                                                <label class="form-check-label text-danger" 
                                                       for="eliminar_video">
                                                    Eliminar
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="video" class="form-control" accept="video/mp4">
                            <small class="form-text text-muted">Formatos permitidos: MP4 (Máx. 50MB)</small>
                        </div>

                        <!-- Sección de Imagen Principal -->
                        <div class="mb-4">
                            <label class="form-label">Imagen Principal</label>
                            <?php if (!empty($publicacion['imagen_principal'])): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="../public/images/propiedades/<?= htmlspecialchars($publicacion['imagen_principal']) ?>" 
                                                 class="rounded" 
                                                 style="width: 80px; height: 60px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <span class="d-block">Imagen actual:</span>
                                                <small class="text-muted"><?= $publicacion['imagen_principal'] ?></small>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="eliminar_imagen" 
                                                       id="eliminar_imagen">
                                                <label class="form-check-label text-danger" 
                                                       for="eliminar_imagen">
                                                    Eliminar
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="imagen_principal" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Formatos permitidos: JPG, PNG, WEBP (Máx. 5MB)</small>
                        </div>

                        <!-- Sección de Imágenes Adicionales -->
                        <div class="mb-4">
                            <label class="form-label">Imágenes Adicionales</label>
                            <?php if (!empty($imagenes_adicionales)): ?>
                                <div class="row row-cols-2 row-cols-md-3 g-3 mb-3">
                                    <?php foreach ($imagenes_adicionales as $img): ?>
                                        <div class="col">
                                            <div class="card h-100 shadow-sm">
                                                <img src="../public/images/propiedades/adicionales/<?= htmlspecialchars($img['imagen_url']) ?>" 
                                                     class="card-img-top" 
                                                     style="height: 100px; object-fit: cover;">
                                                <div class="card-footer bg-transparent">
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               name="eliminar_imagenes[]" 
                                                               value="<?= $img['id'] ?>"
                                                               id="img_<?= $img['id'] ?>">
                                                        <label class="form-check-label text-danger" 
                                                               for="img_<?= $img['id'] ?>">
                                                            Eliminar
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-3">No hay imágenes adicionales</div>
                            <?php endif; ?>

                            <div class="file-upload">
                                <input type="file" 
                                       name="imagenes_adicionales[]" 
                                       class="form-control" 
                                       multiple 
                                       accept="image/*"
                                       id="imagenesAdicionales">
                                <label for="imagenesAdicionales" class="form-label text-muted">
                                    <i class="bi bi-cloud-arrow-up me-2"></i>Seleccionar imágenes...
                                </label>
                                <small class="form-text text-muted d-block">
                                    Formatos permitidos: JPG, PNG, WEBP (Máx. 5MB cada una)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <div class="sticky-bottom bg-body py-4 border-top">
            <div class="container">
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Validación de formulario
(() => {
  'use strict'

  const forms = document.querySelectorAll('.needs-validation')

  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()
</script>

<?php require_once '../includes/admin_footer.php'; ?>