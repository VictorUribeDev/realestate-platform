<?php
// detalle.php
$pageTitle = "Property Details";

// Configurar rutas base
require_once 'includes/config.php';

require_once 'includes/header.php';
require_once 'includes/database.php';

// Validar ID de publicación
if (!isset($_GET['id']) || !$publicacion_id = filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("HTTP/1.1 404 Not Found");
    include '404.php';
    exit();
}

// Consulta optimizada con JOIN
$query = "SELECT p.*, 
         GROUP_CONCAT(i.imagen_url ORDER BY i.orden) AS imagenes 
         FROM publicaciones p 
         LEFT JOIN imagenes i ON p.id = i.publicacion_id 
         WHERE p.id = :id 
         GROUP BY p.id";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $publicacion_id]);
    $publicacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$publicacion) {
        header("HTTP/1.1 404 Not Found");
        include '404.php';
        exit();
    }
    
    $imagenes = !empty($publicacion['imagenes']) ? explode(',', $publicacion['imagenes']) : [];
    
    // Procesar video
    $videoPath = null;
    if (!empty($publicacion['video'])) {
        $videoDir = 'public/videos/propiedades/';
        $absoluteVideoPath = $_SERVER['DOCUMENT_ROOT'] . '/' . basename(dirname(__DIR__)) . '/' . $videoDir . $publicacion['video'];
        $videoPath = BASE_URL . '/' . $videoDir . rawurlencode($publicacion['video']);
    }
    
} catch (PDOException $e) {
    error_log("Error getting publication: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    exit();
}
?>

<div class="container-fluid px-lg-5 py-5">
    <div class="row g-4">
        <!-- Galería Principal -->
        <div class="col-lg-8">
            <?php if (!empty($imagenes)): ?>
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php foreach ($imagenes as $key => $imagen): ?>
                                <button type="button" data-bs-target="#propertyCarousel" 
                                    data-bs-slide-to="<?= $key ?>" 
                                    class="<?= $key === 0 ? 'active' : '' ?>"
                                    aria-label="Imagen <?= $key + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="carousel-inner ratio ratio-16x9">
                            <?php foreach ($imagenes as $key => $imagen): ?>
                                <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                                    <img src="<?= BASE_URL ?>/public/images/propiedades/adicionales/<?= rawurlencode($imagen) ?>" 
                                         class="d-block w-100 object-fit-cover" 
                                         alt="Imagen <?= $key + 1 ?> de <?= htmlspecialchars($publicacion['titulo']) ?>"
                                         loading="lazy"
                                         onerror="this.onerror=null;this.src='<?= BASE_URL ?>/public/images/placeholder.jpg';">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-dark p-3 rounded-circle" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-dark p-3 rounded-circle" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-lg">
                    <div class="ratio ratio-16x9 bg-light d-flex align-items-center justify-content-center">
                        <i class="fas fa-image fa-4x text-muted opacity-50"></i>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($publicacion['video']) && file_exists($absoluteVideoPath)): ?>
                <div class="card border-0 shadow-lg mt-4">
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <video controls class="w-100" poster="<?= BASE_URL ?>/public/images/video-poster.jpg" preload="metadata">
                                <source src="<?= $videoPath ?>" type="video/mp4">
                                Tu navegador no soporta videos HTML5.
                            </video>
                        </div>
                    </div>
                </div>
            <?php elseif (!empty($publicacion['video'])): ?>
                <div class="alert alert-warning mt-4">Video no disponible (Error en el archivo)</div>
            <?php endif; ?>
        </div>

        <!-- Detalles de la Propiedad -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 1rem;">
                <div class="card border-0 shadow-lg mb-4">
                    <div class="card-body">
                        <h1 class="h2 mb-3 fw-bold text-primary"><?= htmlspecialchars($publicacion['titulo']) ?></h1>
                        
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fs-6 py-2 px-3">
                                <i class="fas fa-home me-2"></i><?= ucfirst(htmlspecialchars($publicacion['tipo'])) ?>
                            </span>
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success fs-6 py-2 px-3">
                                <i class="fas fa-hand-holding-usd me-2"></i><?= ucfirst(htmlspecialchars($publicacion['operacion'])) ?>
                            </span>
                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning fs-6 py-2 px-3">
                                <i class="fas fa-check-circle me-2"></i><?= ucfirst(htmlspecialchars($publicacion['estado'])) ?>
                            </span>
                        </div>

                        <div class="bg-light rounded-3 p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small d-block">Precio</span>
                                    <h2 class="h3 text-primary mb-0">
                                        <?php if (!empty($publicacion['precio'])): ?>
                                            <?= htmlspecialchars($publicacion['moneda']) ?> 
                                            <?= number_format(htmlspecialchars($publicacion['precio']), 2) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Consultar</span>
                                        <?php endif; ?>
                                    </h2>
                                </div>
                                <button class="btn btn-primary btn-lg rounded-pill px-4 py-3 shadow-sm" 
                                    onclick="window.location.href = 'contacto.php'">
                                    <i class="fas fa-phone-alt me-2"></i>Contactar
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="text-center p-3 border rounded-3">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-bed fa-2x"></i>
                                    </div>
                                    <div class="fw-bold fs-5"><?= htmlspecialchars($publicacion['num_dormitorios'] ?? '0') ?></div>
                                    <small class="text-muted">Dormitorios</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 border rounded-3">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-bath fa-2x"></i>
                                    </div>
                                    <div class="fw-bold fs-5"><?= htmlspecialchars($publicacion['num_banos'] ?? '0') ?></div>
                                    <small class="text-muted">Baños</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 border rounded-3">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-ruler-combined fa-2x"></i>
                                    </div>
                                    <div class="fw-bold fs-5"><?= number_format(htmlspecialchars($publicacion['superficie_construida']), 0) ?> m²</div>
                                    <small class="text-muted">Construidos</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 border rounded-3">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-expand fa-2x"></i>
                                    </div>
                                    <div class="fw-bold fs-5"><?= number_format(htmlspecialchars($publicacion['superficie_total']), 0) ?> m²</div>
                                    <small class="text-muted">Totales</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="h6 text-uppercase text-muted mb-3"><i class="fas fa-map-marker-alt me-2"></i>Ubicación</h5>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0"><?= htmlspecialchars($publicacion['ubicacion']) ?></p>
                                </div>
                                <button class="btn btn-link text-primary p-0" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#mapModal">
                                    <i class="fas fa-map-marked-alt"></i>
                                </button>
                            </div>
                        </div>

                        <?php if (!empty($publicacion['descripcion'])): ?>
                            <div class="border-top pt-4">
                                <h5 class="h6 text-uppercase text-muted mb-3"><i class="fas fa-file-alt me-2"></i>Descripción</h5>
                                <div class="text-secondary lh-lg">
                                    <?= nl2br(htmlspecialchars($publicacion['descripcion'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Contacto -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="contactModalLabel">Contactar sobre <?= htmlspecialchars($publicacion['titulo']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensaje</label>
                        <textarea class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mapa -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="mapModalLabel">Ubicación en Mapa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 ratio ratio-16x9">
                <?php if (!empty($publicacion['mapa_url'])): ?>
                    <iframe 
                        src="<?= htmlspecialchars($publicacion['mapa_url']) ?>"
                        class="w-100 h-100"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        sandbox="allow-scripts allow-same-origin"
                        title="Mapa de ubicación de la propiedad">
                    </iframe>
                <?php else: ?>
                    <div class="bg-light d-flex flex-column align-items-center justify-content-center h-100">
                        <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted text-center">Ubicación no disponible<br>
                            <small>El vendedor no ha proporcionado un mapa</small>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
// CSS personalizado mejorado
$custom_css = '
    .carousel-item {
        transition: transform 0.6s ease-in-out;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-size: 60%;
    }
    .object-fit-cover {
        object-fit: cover;
    }
    .sticky-top {
        z-index: 1020;
    }
    .hover-shadow {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15)!important;
    }
';
require_once 'includes/footer.php';
?>