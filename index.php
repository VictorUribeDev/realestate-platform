<?php
$pageTitle = "Real Estate Platform | Properties for Sale and Rent";
$metaDescription = "Find your ideal property. Houses, apartments and land for sale and rent. Professional real estate brokerage service.";
$metaKeywords = "properties, houses for sale, apartments for rent, real estate, realtor [Your City]";
// index.php
$pageTitle = "Home";

// Verificar conexión a la base de datos ANTES de cualquier operación
require_once 'includes/database.php';
if (!$pdo) {
    die("Database connection error");
}

// Configurar rutas base - use config.php instead
require_once 'includes/config.php';

require_once 'includes/header.php';

// Manejo de parámetros GET
$tipo_seleccionado = $_GET['tipo'] ?? '';
$operacion_seleccionada = $_GET['operacion'] ?? '';

try {
    // Construcción de consulta segura
    $query = "SELECT * FROM publicaciones WHERE estado = 'disponible' ORDER BY id DESC";
    $params = [];
    $types = '';

    if (!empty($tipo_seleccionado)) {
        $query .= " AND tipo = :tipo";
        $params[':tipo'] = $tipo_seleccionado;
    }

    if (!empty($operacion_seleccionada)) {
        $query .= " AND operacion = :operacion";
        $params[':operacion'] = $operacion_seleccionada;
    }

    $stmt = $pdo->prepare($query);
    
    // Ejecutar con parámetros nombrados
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}  
?>

<div class="container-fluid position-relative p-0">  
    <div id="video-banner" class="video-banner bg-dark text-white">  
        <div class="d-flex justify-content-between align-items-center p-2">  
            <p class="mb-0">¡Mira nuestro video destacado!</p>  
            <button id="close-banner" class="btn btn-sm btn-danger">Cerrar</button>  
        </div>  
        <div id="video-container" class="w-100">  
            <div id="youtube-player"></div>  
        </div>  
    </div>  
</div>  

<div class="container mt-5">  
    <div class="text-center">  
        <h1>Welcome to Real Estate Platform</h1>  
        <p>Find your ideal home and learn more about our services.</p>  
    </div>  
    <form method="GET" class="bg-light p-4 rounded shadow-sm">  
        <div class="row g-3 align-items-center">  
            <div class="col-md-4">  
                <label for="tipo" class="form-label fw-bold">Tipo de Propiedad</label>  
                <select name="tipo" id="tipo" class="form-select">  
                    <option value="" <?= empty($tipo_seleccionado) ? 'selected' : '' ?>>Selecciona...</option>  
                    <option value="casa" <?= $tipo_seleccionado == 'casa' ? 'selected' : '' ?>>Casa</option>  
                    <option value="departamento" <?= $tipo_seleccionado == 'departamento' ? 'selected' : '' ?>>Departamento</option>  
                    <option value="parcela" <?= $tipo_seleccionado == 'parcela' ? 'selected' : '' ?>>Parcela</option>  
                    <option value="oficina" <?= $tipo_seleccionado == 'oficina' ? 'selected' : '' ?>>Oficina</option>  
                </select>  
            </div>  
            <div class="col-md-4">  
                <label for="operacion" class="form-label fw-bold">Tipo de Operación</label>  
                <select name="operacion" id="operacion" class="form-select">  
                    <option value="" <?= empty($operacion_seleccionada) ? 'selected' : '' ?>>Selecciona...</option>  
                    <option value="venta" <?= $operacion_seleccionada == 'venta' ? 'selected' : '' ?>>Venta</option>  
                    <option value="arriendo" <?= $operacion_seleccionada == 'arriendo' ? 'selected' : '' ?>>Arriendo</option>  
                </select>  
            </div>  
            <div class="col-md-4 text-center">  
                <button type="submit" class="btn btn-primary w-100 mt-4">Buscar</button>  
            </div>  
        </div>  
    </form>  
</div>  

<div class="container mt-4">  
    <div class="row">  
        <?php if (!empty($publicaciones)): ?>  
            <?php foreach ($publicaciones as $publicacion): ?>  
                <div class="col-md-4 mb-4 propiedades-card">  
                    <div class="card h-100">  
                        <div class="image-container">  
                            <!-- Ruta corregida con BASE_URL -->  
                            <img src="<?= BASE_URL ?>/public/images/propiedades/<?= htmlspecialchars($publicacion['imagen_principal']) ?>"   
                                 class="card-img-top"   
                                 alt="<?= htmlspecialchars($publicacion['titulo']) ?>"  
                                 onerror="this.onerror=null;this.src='<?= BASE_URL ?>/public/images/placeholder.jpg';">  
                                 
                            <?php if (!empty($publicacion['video'])): ?>  
                                <div class="video-indicator">  
                                    <i class="fa-solid fa-video"></i>  
                                </div>  
                            <?php endif; ?>  
                        </div>  
                        <div class="card-body">  
                            <h5 class="card-title">  
                                <?= !empty($publicacion['titulo'])   
                                    ? htmlspecialchars($publicacion['titulo'])  
                                    : ucfirst(htmlspecialchars($publicacion['tipo'])) . ' - ' . ucfirst(htmlspecialchars($publicacion['operacion'])) ?>  
                            </h5>  
                            
                            <p class="card-text"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($publicacion['ubicacion'] ?? 'Ubicación no disponible') ?></p>  
                            
                            <div class="property-details">  
                                <p class="price">  
                                    <?php if (!empty($publicacion['precio'])): ?>  
                                        <?= htmlspecialchars($publicacion['moneda']) ?> <?= number_format($publicacion['precio'], 2) ?>  
                                    <?php else: ?>  
                                        <span class="text-muted">Consultar precio</span>  
                                    <?php endif; ?>  
                                </p>  
                                
                                <div class="row specs">  
                                    <div class="col-6">  
                                        <p><i class="fa-solid fa-bed"></i> <?= $publicacion['num_dormitorios'] ?? 'N/A' ?> Dorm.</p>  
                                    </div>  
                                    <div class="col-6">  
                                        <p><i class="fa-solid fa-bath"></i> <?= $publicacion['num_banos'] ?? 'N/A' ?> Baños</p>  
                                    </div>  
                                    <div class="col-6">  
                                        <p><i class="fa-solid fa-maximize"></i> <?= number_format($publicacion['superficie_construida'] ?? 0, 2) ?> m²</p>  
                                    </div>  
                                    <div class="col-6">  
                                        <p><i class="fa-solid fa-vector-square"></i> <?= number_format($publicacion['superficie_total'] ?? 0, 2) ?> m²</p>  
                                    </div>  
                                </div>  
                            </div>  
                            
                            <a href="detalle.php?id=<?= $publicacion['id'] ?>" class="btn btn-primary w-100">  
                                <i class="fa-solid fa-circle-info"></i> Ver Detalles  
                            </a>  
                        </div>  
                    </div>  
                </div>  
            <?php endforeach; ?>  
        <?php else: ?>  
            <div class="col-12">  
                <div class="alert alert-info text-center">  
                    No se encontraron propiedades con los filtros seleccionados  
                </div>  
            </div>  
        <?php endif; ?>  
    </div>  
</div>  

<?php  
$custom_js = BASE_URL . '/public/js/youtube-banner.js'; // Path fixed  
require_once 'includes/footer.php';  
?>  