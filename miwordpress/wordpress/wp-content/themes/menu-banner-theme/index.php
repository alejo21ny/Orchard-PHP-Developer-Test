<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Banner</title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>"> 
    <?php wp_head(); ?>
</head>
<body>
  
   <nav class="main-menu">
 <?php
wp_nav_menu([
    'theme_location' => 'primary',  
    'container' => 'nav',           
    'container_class' => 'main-menu-container',  
    'menu_class' => 'main-menu',    
    'depth' => 0,                   
    'fallback_cb' => false,         
]);
?>



    </nav>
    <div class="banner" style="background-image: url('<?php echo get_banner_image(); ?>');">
    </div>

  
    
    <div class="product-day">
        <?php echo pod_display_product_of_the_day(); ?>
    </div>


    <?php wp_footer(); ?>
</body>
</html>
