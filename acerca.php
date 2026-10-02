<?php
// index.php
$pageTitle = "About Us";
require_once 'includes/header.php';?>
<?php ob_start(); ?>
<div id="sobre-flies-propiedades" class="container">
    <h1 class="text-center">About Real Estate Platform</h1>
    <p class="text-center">
        At Real Estate Platform, we combine years of experience with unmatched dedication to offer you a complete service in the real estate sector.
        We specialize in helping you find your ideal home and maximizing the value of your properties.
    </p>

    <div id="flies-cards" class="row">
        <?php 
        $servicios = [
            ['icon' => 'fa-bullhorn', 'title' => 'Property Marketing', 'text' => 'We boost the visibility of your properties with advanced marketing strategies to attract qualified buyers.'],
            ['icon' => 'fa-handshake', 'title' => 'Real Estate Advisory', 'text' => 'We provide professional guidance to make sound decisions in buying, selling or renting properties.'],
            ['icon' => 'fa-home', 'title' => 'Buy/Sell Homes', 'text' => 'We facilitate the process of acquiring or selling your home, ensuring fast and secure transactions.']
        ];

        foreach ($servicios as $servicio) {
            echo '<div class="col-md-4 flies-card">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas ' . $servicio['icon'] . ' card-icon"></i>
                            <h5 class="card-title">' . $servicio['title'] . '</h5>
                            <p class="card-text">' . $servicio['text'] . '</p>
                        </div>
                    </div>
                  </div>';
        }
        ?>
    </div>
</div>
<?php
require_once 'includes/footer.php';?>



