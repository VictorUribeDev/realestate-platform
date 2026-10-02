<?php
session_start();
require_once '../includes/database.php';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Obtener mensajes
try {
    $mensajes = $pdo->query("SELECT * FROM mensajes ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar mensajes: " . $e->getMessage());
}

require_once '../includes/admin_header.php';
?>

<style>
    /* Estilos mejorados para el layout */
    * {
        box-sizing: border-box;
    }

    .sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: #1a1a1a;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
        overflow-y: auto;
    }
    
    .main-content {
        margin-left: 280px; /* Espacio fijo para el sidebar */
        transition: all 0.3s ease-in-out;
        min-height: 100vh;
        background: #f8f9fa;
        padding: 1rem;
        width: calc(100% - 280px); /* Ancho calculado */
        overflow-x: hidden; /* Prevenir desbordamiento horizontal */
    }
    
    .sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid #2d2d2d;
    }
    
    .sidebar-link {
        color: #b3b3b3;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s;
        text-decoration: none;
        display: block;
    }
    
    .sidebar-link:hover,
    .sidebar-link.active {
        color: #fff;
        background: #2d2d2d;
    }
    
    .mobile-menu-btn {
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1100;
        display: none;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
        
        .mobile-menu-btn {
            display: block;
        }
        
        .modal-dialog {
            margin: 1rem;
        }
    }

    /* Tabla responsive */
    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }
    
    .table {
        min-width: 800px; /* Ancho mínimo para mantener estructura */
        width: 100%;
    }
</style>

<button class="btn btn-dark mobile-menu-btn" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-comments me-2 text-primary"></i>Mensajes
                        </h1>
                        <p class="text-muted mb-0"><?= count($mensajes) ?> mensajes encontrados</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filtrosModal">
                            <i class="fas fa-filter me-2"></i>Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tarjeta de mensajes -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-wrapper"> <!-- Wrapper para scroll horizontal -->
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Contacto</th>
                                        <th>Mensaje</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensajes as $msg): ?>
                                    <tr class="align-middle">
                                        <td>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($msg['fecha'])) ?></small><br>
                                            <small><?= date('H:i', strtotime($msg['fecha'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                    <?= strtoupper(substr($msg['nombre'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($msg['nombre']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($msg['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 250px;">
                                                <?= htmlspecialchars($msg['mensaje']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Nuevo</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#mensajeModal<?= $msg['id'] ?>"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="mailto:<?= $msg['email'] ?>" 
                                                   class="btn btn-sm btn-outline-success"
                                                   title="Responder">
                                                    <i class="fas fa-reply"></i>
                                                </a>
                                                <form method="POST" action="eliminar_mensaje.php">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
    <button type="submit" class="btn btn-sm btn-outline-danger"
            onclick="return confirm('¿Eliminar este mensaje?')">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal -->
                                    <div class="modal fade" id="mensajeModal<?= $msg['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-envelope-open me-2"></i>
                                                        Mensaje de <?= htmlspecialchars($msg['nombre']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-12">
                                                            <label class="form-label text-muted small mb-1">Fecha</label>
                                                            <p class="mb-0"><?= date('d/m/Y H:i', strtotime($msg['fecha'])) ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label text-muted small mb-1">Email</label>
                                                            <p class="mb-0"><?= htmlspecialchars($msg['email']) ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label text-muted small mb-1">Teléfono</label>
                                                            <p class="mb-0"><?= $msg['telefono'] ? htmlspecialchars($msg['telefono']) : 'N/A' ?></p>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label text-muted small mb-1">Mensaje</label>
                                                            <div class="border p-3 rounded bg-light">
                                                                <?= nl2br(htmlspecialchars($msg['mensaje'])) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    <a href="mailto:<?= $msg['email'] ?>" class="btn btn-primary">
                                                        <i class="fas fa-reply me-2"></i>Responder
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>

<script>
// Toggle del sidebar
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('.main-content');
const sidebarToggle = document.getElementById('sidebarToggle');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('active');
});

// Cerrar sidebar al hacer clic fuera en móviles
document.addEventListener('click', (e) => {
    if (window.innerWidth < 768 && 
        !sidebar.contains(e.target) && 
        !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('active');
        mainContent.classList.remove('active');
    }
});

// Ajustar layout al cambiar tamaño
window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
        sidebar.classList.remove('active');
        mainContent.classList.remove('active');
    }
});
</script>