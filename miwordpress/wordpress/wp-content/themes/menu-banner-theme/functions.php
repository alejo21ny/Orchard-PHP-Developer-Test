<?php
function menu_banner_theme_setup() {
   
    register_nav_menus([
        'primary' => __('Menú Principal'),
    ]);
}
add_action('after_setup_theme', 'menu_banner_theme_setup');

function get_banner_image() {
    $locations = get_nav_menu_locations();
    $menu = wp_get_nav_menu_object($locations['primary']);
    $menu_items = wp_get_nav_menu_items($menu->term_id);

    
    $image_a = get_template_directory_uri() . '/images/banner-a.jpg';
    $image_b = get_template_directory_uri() . '/images/banner-b.jpg';

    foreach ($menu_items as $item) {
        if (is_page($item->object_id)) {
            
            if (strpos($item->title, 'Root A') !== false || strpos($item->title, 'Sub 1') !== false ) {
                return $image_a;
            }
            
            elseif (strpos($item->title, 'Root B') !== false || strpos($item->title, 'Sub 2') !== false || strpos($item->title, 'Sub 3') !== false) {
                return $image_b;
            }
        }
    }


    return $image_a;
}


function enqueue_custom_stylesheets() {
    wp_enqueue_style('custom-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_stylesheets');


