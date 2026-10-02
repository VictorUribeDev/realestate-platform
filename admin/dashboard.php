<?php
session_start();
require_once '../includes/database.php';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Obtener datos con manejo de errores
try {
    $publicaciones = $pdo->query("SELECT * FROM publicaciones ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $mensajes = $pdo->query("SELECT * FROM mensajes ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar datos: " . $e->getMessage());
}

require_once '../includes/admin_header.php';
?>
<style>
    .sidebar { width: 250px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; overflow-y: auto; transition: all 0.3s; }
    .main-content { margin-left: 250px; width: calc(100% - 250px); min-height: 100vh; transition: all 0.3s; }
    @media (max-width: 768px) {
        .sidebar { margin-left: -250px; }
        .sidebar.active { margin-left: 0; }
        .main-content { width: 100%; margin-left: 0; }
    }
</style>


        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="publicaciones">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Gestión de Propiedades</h2>
                        <a href="agregar_publicacion.php" class="btn btn-success"><i class="fas fa-plus-circle me-2"></i>Nueva Publicación</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Operación</th>
                                    <th>Ubicación</th>
                                    <th>Baños</th>
                                    <th>Dormitorios</th>
                                    <th>Sup. Construida</th>
                                    <th>Sup. Total</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($publicaciones as $pub): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pub['id']) ?></td>
                                    <td><?= htmlspecialchars($pub['tipo'] ?? 'N/A') ?></td>
                                    <td><?= ucfirst(htmlspecialchars($pub['operacion'] ?? 'No especificado')) ?></td>
                                    <td><?= htmlspecialchars($pub['ubicacion'] ?? 'Sin ubicación') ?></td>
                                    <td><?= htmlspecialchars($pub['num_banos'] ?? '0') ?></td>
                                    <td><?= htmlspecialchars($pub['num_dormitorios'] ?? '0') ?></td>
                                    <td><?= htmlspecialchars(number_format($pub['superficie_construida'] ?? 0, 2)) ?> m²</td>
                                    <td><?= htmlspecialchars(number_format($pub['superficie_total'] ?? 0, 2)) ?> m²</td>
                                    <td><?= htmlspecialchars($pub['moneda'] ?? 'USD') ?> <?= number_format($pub['precio'] ?? 0, 2) ?></td>
                                    <td>
    <div class="btn-group">
        <a href="editar_publicacion.php?id=<?= $pub['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
        <form method="POST" action="eliminar_publicacion.php" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
            <input type="hidden" name="id" value="<?= $pub['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger" 
                    onclick="return confirm('¿Está seguro de eliminar esta propiedad permanentemente?')"
                    title="Eliminar">
                <i class="fas fa-trash-alt"></i>
            </button>
        </form>
    </div>
</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>