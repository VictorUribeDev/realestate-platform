<?php
// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si no está autenticado
if (!isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $pageTitle ?? 'Admin - Real Estate Platform' ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos admin -->
    <style>
        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            width: 280px;
            transition: all 0.3s;
            z-index: 1000;
            background: #2c3e50;
        }
        
        .main-content {
            margin-left: 280px;
            transition: all 0.3s;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
        
        .nav-link {
            color: #ecf0f1 !important;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: #34495e;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar bg-dark text-white p-3">
        <div class="sidebar-header">
            <h3 class="mb-4">Admin Panel</h3>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="agregar_publicacion.php">
                    <i class="fas fa-plus-circle me-2"></i>Nueva Publicación
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="mensajes.php">
                    <i class="fas fa-envelope me-2"></i>Mensajes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </a>
            </li>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="main-content">
        <!-- Barra superior -->
        <nav class="navbar navbar-expand navbar-light bg-light shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-dark d-md-none" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-text ms-auto">
                    <i class="fas fa-user-circle me-2"></i><?= $_SESSION['admin_user'] ?? 'Admin' ?>
                </span>
            </div>
        </nav>

        <!-- Contenedor de contenido -->
         <!-- Modal de Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="lead">¿Estás seguro que deseas salir del panel de administración?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="logout.php" class="btn btn-danger">Sí, Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

        <div class="container-fluid p-4">


        <script>
    // Toggle sidebar en móviles
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
</script>