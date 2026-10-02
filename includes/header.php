<?php
// index.php

// Keywords dinámicas según filtros
$baseKeywords = [
    'propiedades',
    'inmobiliaria',
    'venta de casas',
    'arriendo departamentos',
    'bienes raíces',
    'tu ciudad'
];

// Keywords contextuales según búsqueda
$filterKeywords = [];
if (!empty($tipo_seleccionado)) {
    $filterKeywords[] = $tipo_seleccionado;
    $filterKeywords[] = $tipo_seleccionado . ' en venta';
    $filterKeywords[] = $tipo_seleccionado . ' en arriendo';
}

if (!empty($operacion_seleccionada)) {
    $filterKeywords[] = $operacion_seleccionada . ' de propiedades';
    $filterKeywords[] = $operacion_seleccionada . ' ' . ($tipo_seleccionado ?: 'inmueble');
}

// Combinar y eliminar duplicados
$finalKeywords = array_unique(array_merge($baseKeywords, $filterKeywords));

// Limitar a 15 términos y formatear
$metaKeywords = implode(', ', array_slice($finalKeywords, 0, 15));
?>

<!-- En header.php -->
<meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Real Estate Platform'; ?></title>
    
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="public/images/logo.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/images/logo.png" alt="Real Estate Platform" style="height: 100px;">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="propiedades.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="acerca.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacto.php">Contact</a>
                    </li>
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="admin/dashboard.php">Admin Panel</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container my-4">