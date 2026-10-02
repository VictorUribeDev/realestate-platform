<?php
// index.php
$pageTitle = "Properties";
require_once 'includes/header.php';
require_once 'includes/config.php';
// Obtener publicaciones de la base de datos
require_once 'includes/database.php';
$stmt = $pdo->query("SELECT * FROM publicaciones");
$publicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
require_once 'includes/footer.php';
?>
