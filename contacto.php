<?php
// contacto.php
$pageTitle = "Contact Us";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="text-center">Contact Us</h2>
    <p class="text-center">If you have any questions, feel free to write or call us.</p>

    <!-- Formulario de contacto -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="<?= BASE_URL ?>procesar_contacto.php" method="POST">
                <?php if(isset($_SESSION['csrf_token'])): ?>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Name</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="telefono" class="form-label">Phone (optional)</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="mb-3">
                    <label for="mensaje" class="form-label">Message</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Contacto directo -->
    <div class="text-center mt-4">
        <p>Or you can reach us at:</p>
        <div class="d-flex justify-content-center">
            <a href="https://wa.me/YOUR_PHONE_NUMBER" target="_blank" class="text-success me-4 social-icon">
                <i class="fab fa-whatsapp fa-2x"></i>
            </a>
            <a href="mailto:your-email@example.com" target="_blank" class="text-danger social-icon">
                <i class="fas fa-envelope fa-2x"></i>
            </a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>