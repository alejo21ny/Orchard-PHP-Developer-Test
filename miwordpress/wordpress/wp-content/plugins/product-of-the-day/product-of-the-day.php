<?php
/*
Plugin Name: Producto del Día
Description: Plugin para mostrar un producto del día aleatorio.
Version: 1.0
Author: Root
*/


function pod_create_product_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'productos';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        summary text NOT NULL,
        image_url varchar(255) NOT NULL,
        is_product_of_the_day tinyint(1) DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'pod_create_product_table');

function pod_create_clicks_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cta_clicks';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id mediumint(9) NOT NULL,
        click_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'pod_create_clicks_table');


function pod_add_admin_menu() {
    add_menu_page('Productos', 'Productos', 'manage_options', 'productos', 'pod_product_page');
}
add_action('admin_menu', 'pod_add_admin_menu');


function pod_product_page() {
    global $wpdb;
    $message = '';

    
    if (isset($_POST['submit_product'])) {
        $name = sanitize_text_field($_POST['product_name']);
        $summary = sanitize_textarea_field($_POST['product_summary']);
        $image_url = esc_url($_POST['product_image_url']);
        $is_product_of_the_day = isset($_POST['is_product_of_the_day']) ? 1 : 0;

     
        if ($is_product_of_the_day) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}productos WHERE is_product_of_the_day = 1");
            if ($count >= 5) {
                $is_product_of_the_day = 0; // Evitar que más de 5 productos tengan la marca
                $message = 'No se puede agregar más de 5 productos como Producto del Día.';
            }
        }

        
        $wpdb->insert(
            "{$wpdb->prefix}productos",
            [
                'name' => $name,
                'summary' => $summary,
                'image_url' => $image_url,
                'is_product_of_the_day' => $is_product_of_the_day,
            ]
        );
        if (!$message) {
            $message = 'Producto agregado correctamente!';
        }
    }

    
    echo '<div class="wrap">';
    echo '<h1>Agregar Producto</h1>';

    if (!empty($message)) {
        echo '<div class="notice notice-success"><p>' . $message . '</p></div>';
    }

    echo '
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th><label for="product_name">Nombre del Producto</label></th>
                <td><input type="text" id="product_name" name="product_name" required class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="product_summary">Resumen del Producto</label></th>
                <td><textarea id="product_summary" name="product_summary" required class="large-text"></textarea></td>
            </tr>
            <tr>
                <th><label for="product_image_url">URL de la Imagen</label></th>
                <td><input type="url" id="product_image_url" name="product_image_url" required class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="is_product_of_the_day">¿Marcar como Producto del Día?</label></th>
                <td><input type="checkbox" id="is_product_of_the_day" name="is_product_of_the_day" value="1" /></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit_product" id="submit_product" class="button button-primary" value="Agregar Producto">
        </p>
    </form>';
    echo '</div>';

    
    $productos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}productos");

    if ($productos) {
        echo '<h2>Productos Agregados</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Nombre</th><th>Resumen</th><th>Imagen</th><th>Producto del Día</th></tr></thead>';
        echo '<tbody>';

        foreach ($productos as $producto) {
            echo '<tr>';
            echo '<td>' . $producto->id . '</td>';
            echo '<td>' . $producto->name . '</td>';
            echo '<td>' . $producto->summary . '</td>';
            echo '<td><img src="' . $producto->image_url . '" style="width: 100px;" /></td>';
            echo '<td>' . ($producto->is_product_of_the_day ? 'Sí' : 'No') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No hay productos añadidos aún.</p>';
    }
}


function pod_display_product_of_the_day() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'productos';

    
    $product = $wpdb->get_row("SELECT * FROM $table_name WHERE is_product_of_the_day = 1 ORDER BY RAND() LIMIT 1");

    if ($product) {
        return "
        <div class='product-of-the-day'>
            <h2>Producto del Día: {$product->name}</h2>
            <img src='{$product->image_url}' alt='{$product->name}' style='max-width: 300px; height: auto;' />
            <p>{$product->summary}</p>
            <a href='#' class='btn btn-primary' onclick='trackClick({$product->id})'>Ver más</a>
        </div>
        <script>
        function trackClick(productId) {
            jQuery.post('" . admin_url('admin-ajax.php') . "', {
                action: 'pod_track_click',
                product_id: productId
            });
        }
        </script>";
    } else {
        return '<p>No hay productos disponibles como "Producto del Día" en este momento.</p>';
    }
}
add_shortcode('product_of_the_day', 'pod_display_product_of_the_day');


function pod_track_click() {
    global $wpdb;
    $product_id = intval($_POST['product_id']);
    $wpdb->insert("{$wpdb->prefix}cta_clicks", ['product_id' => $product_id]);
    wp_send_json_success();
}
add_action('wp_ajax_nopriv_pod_track_click', 'pod_track_click');
add_action('wp_ajax_pod_track_click', 'pod_track_click');


function pod_schedule_weekly_email() {
    if (!wp_next_scheduled('pod_send_weekly_email')) {
        wp_schedule_event(strtotime('next Monday 2:00'), 'weekly', 'pod_send_weekly_email');
    }
}
add_action('wp', 'pod_schedule_weekly_email');

function pod_send_weekly_email() {
    global $wpdb;
    $admin_email = get_option('admin_email');
    $productos = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}productos WHERE is_product_of_the_day = 1");

    
    $email_content = "Productos del Día de la semana:\n\n";
    foreach ($productos as $producto) {
        $clicks = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cta_clicks WHERE product_id = {$producto->id}");
        $email_content .= "{$producto->name} - Clics: {$clicks}\n";
    }

    
    wp_mail($admin_email, 'Resumen de Productos del Día', $email_content);
}
add_action('pod_send_weekly_email', 'pod_send_weekly_email');
?>
