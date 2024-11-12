<?php
// Archivo: includes/class-dp-shortcodes.php

if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class DP_Shortcodes {

    private static $instance = null;
    private $api_key;
    private $user_lat = 0;
    private $user_lng = 0;
    private $search_radius = 50; // Radio en km
    private $debug_mode = false;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_Shortcodes();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->api_key = get_option('dp_google_maps_api_key'); // Recupera la API Key desde las opciones del plugin
        $this->search_radius = get_option('dp_search_radius', 50); // Recupera el radio de búsqueda
        $this->debug_mode = get_option('dp_debug_mode', false); // Recupera el modo de depuración
        add_shortcode('directorio_profesionales', array($this, 'directorio_profesionales_shortcode'));
    }

    public function directorio_profesionales_shortcode($atts) {
        global $wpdb;

        if (empty($this->api_key)) {
            echo '<p>La clave API de Google Maps no está configurada. Por favor, <a href="' . admin_url('options-general.php?page=dp-settings') . '">configúrala aquí</a>.</p>';
            return;
        }

        ob_start();

        $custom_css = get_option('dp_custom_css');
        if (!empty($custom_css)) {
            echo '<style>' . esc_html($custom_css) . '</style>';
        }

        // Obtener los parámetros de filtrado
        $nombre = isset($_GET['nombre']) ? sanitize_text_field($_GET['nombre']) : '';
        $genero = isset($_GET['genero']) ? sanitize_text_field($_GET['genero']) : '';
        $direccion_usuario = isset($_GET['direccion_usuario']) ? sanitize_text_field($_GET['direccion_usuario']) : '';

        // Geocodificar la dirección del usuario si está presente
        if (!empty($direccion_usuario)) {
            $this->geocode_user_address($direccion_usuario);
        }

        // Mostrar el formulario de filtrado
        $this->display_filter_form($genero, $direccion_usuario, $nombre);

        // Realizar la consulta de profesionales
        $query = $this->get_profesionales_query($genero, $nombre);

        // Mostrar los resultados
        $this->display_profesionales($query);

        // Mostrar la información de depuración
        $this->display_debug_info($direccion_usuario, $genero, isset($query) ? $query->request : 'N/A');

        return ob_get_clean();
    }

    private function geocode_user_address($direccion_usuario) {
        // Realizar la geocodificación
        $url = add_query_arg(array(
            'address' => urlencode($direccion_usuario),
            'key'     => $this->api_key,
        ), 'https://maps.googleapis.com/maps/api/geocode/json');

        $response = wp_remote_get($url);

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if ($data->status === 'OK') {
                $this->user_lat = $data->results[0]->geometry->location->lat;
                $this->user_lng = $data->results[0]->geometry->location->lng;
            } else {
                echo '<p>Ubicación no encontrada. Por favor, verifica la dirección ingresada.</p>';
            }
        } else {
            echo '<p>Error al geocodificar la ubicación. Por favor, intenta de nuevo más tarde.</p>';
        }
    }

    private function display_filter_form($genero, $direccion_usuario, $nombre) {
        // Obtener la URL actual sin parámetros de búsqueda
        $current_url = remove_query_arg(array('genero', 'direccion_usuario', 'nombre', 'latitud_usuario', 'longitud_usuario'));
        ?>
        <div id="section-form-filtro-professionales">
            <form method="GET" id="form-filtro-profesionales" class="filter-form">
                <div class="form-group">
                    <label for="nombre">Nombre del doctor:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre del doctor" value="<?php echo esc_attr($nombre); ?>" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="direccion_usuario">Tu población:</label>
                    <input type="text" id="direccion_usuario" name="direccion_usuario" placeholder="Ej: Barcelona" value="<?php echo esc_attr($direccion_usuario); ?>" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="genero">Tipo de tratamiento:</label>
                    <select id="genero" name="genero">
                        <option value="">Todos</option>
                        <option value="Masculino" <?php selected($genero, 'Masculino'); ?>>Tratamiento masculino</option>
                        <option value="Femenino" <?php selected($genero, 'Femenino'); ?>>Tratamiento femenino</option>
                        <option value="Transgenero" <?php selected($genero, 'Transgenero'); ?>>Tratamiento transgénero</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="search-button">Buscar</button>
                    <a href="<?php echo esc_url($current_url); ?>" class="reset-filters-button">Mostrar Todos</a>
                </div>
                <div id="loading-spinner" style="display:none;">
                    <img src="<?php echo esc_url(DP_PLUGIN_URL . 'assets/images/spinner.gif'); ?>" alt="Cargando..." />
                </div>
            </form>
        </div>
        <?php
    }

    private function get_profesionales_query($genero, $nombre) {
        $args = array(
            'post_type'      => 'profesional',
            'posts_per_page' => -1,
            'meta_query'     => array(),
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if (!empty($genero)) {
            $args['meta_query'][] = array(
                'key'     => 'genero_profesional',
                'value'   => $genero,
                'compare' => '=',
            );
        }
        if(!empty($nombre)){
            $args['meta_query'][] = array(
                    'key'     => 'nombre_profesional',
                    'value'   => $nombre,
                    'compare' => 'LIKE',
            );
        }

        if ($this->user_lat && $this->user_lng) {
            add_filter('posts_clauses', array($this, 'modify_posts_clauses'));
            $args['meta_query'][] = array(
                'relation' => 'AND',
                array(
                    'key'     => 'latitud_profesional',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key'     => 'longitud_profesional',
                    'compare' => 'EXISTS',
                ),
            );
        }

        $query = new WP_Query($args);

        if ($this->user_lat && $this->user_lng) {
            remove_filter('posts_clauses', array($this, 'modify_posts_clauses'));
        }

        return $query;
    }

    private function display_profesionales($query) {
        if ($query->have_posts()) {
            echo '<ul class="directorio-profesionales">';
            while ($query->have_posts()) {
                $query->the_post();

                // Obtener campos personalizados
                $nombre     = get_field('nombre_profesional');
                $centro     = get_field('centro_medico');
                $direccion  = get_field('direccion_profesional');
                $poblacion  = get_field('poblacion_profesional');
                $correo     = get_field('correo_profesional');
                $telefono   = get_field('telefono_profesional');
                $biografia  = get_field('biografia_profesional');
                $imagen     = get_field('imagen_profesional');
                $logo       = get_field('logo_clinica_profesional');
                $link       = get_field('link_profesional');
                $url        = get_field('url_profesional');
                $genero     = get_field('genero_profesional');

                // Obtener la distancia calculada
                global $post;
                $distance = isset($post->distance) ? round($post->distance, 2) : 0;

                // Filtrar por radio en PHP
                if ($distance > $this->search_radius) {
                    continue;
                }
                $distance_display = $distance . ' km';

                ?>
                <li class="profesional-item">
                    <div class="profesional-card">
                        <?php if ($imagen): ?>
                            <img class="profesional-img" src="<?php echo esc_url($imagen['url']); ?>" alt="<?php echo esc_attr($imagen['alt']); ?>">
                        <?php endif; ?>
                        <div class="profesional-info">
                            <h3 class="profesional-nombre">
                                <a href="<?php the_permalink(); ?>"><?php echo esc_html($nombre); ?></a>
                            </h3>
                            <p class="profesional-centro"><?php echo esc_html($centro); ?></p>
                            <p><strong>Dirección:</strong> <?php echo esc_html($direccion . ', ' . $poblacion); ?></p>
                            <p><strong>Teléfono:</strong> <a href="tel:<?php echo esc_attr($telefono); ?>"><?php echo esc_html($telefono); ?></a></p>
                            <p><strong>Correo:</strong> <a href="mailto:<?php echo esc_attr($correo); ?>"><?php echo esc_html($correo); ?></a></p>
                            <?php if ($logo): ?>
                                <img class="profesional-logo" src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>">
                            <?php endif; ?>
                            <?php if (isset($post->distance)): ?>
                                <p><strong>Distancia:</strong> <?php echo esc_html($distance_display); ?></p>
                            <?php endif; ?>
                            <?php if ($url): ?>
                                <a class="ver-perfil-btn" href="<?php echo esc_url($url); ?>" target="_blank">Ver perfil</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php
            }
            echo '</ul>';
            wp_reset_postdata();

        } else {
            echo '<p>No se encontraron profesionales que coincidan con los criterios de búsqueda.</p>';
        }
    }


    private function display_debug_info($direccion_usuario, $genero, $sql_query) {
        if ($this->debug_mode) {
            ?>
            <div id="dp-debug" style="background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin-top: 20px;">
                <h4>Debugging Information</h4>
                <p><strong>Dirección del Usuario:</strong> <?php echo esc_html($direccion_usuario); ?></p>
                <p><strong>Latitud del Usuario:</strong> <?php echo esc_html($this->user_lat); ?></p>
                <p><strong>Longitud del Usuario:</strong> <?php echo esc_html($this->user_lng); ?></p>
                <p><strong>Género Seleccionado:</strong> <?php echo esc_html($genero); ?></p>
                <p><strong>Consulta SQL:</strong> <?php echo esc_html($sql_query); ?></p>
            </div>
            <?php
        }
    }

    public function modify_posts_clauses($clauses) {
        global $wpdb;

        // Añadir los JOIN necesarios
        $clauses['join'] .= " INNER JOIN {$wpdb->postmeta} AS latitud_prof ON {$wpdb->posts}.ID = latitud_prof.post_id AND latitud_prof.meta_key = 'latitud_profesional'";
        $clauses['join'] .= " INNER JOIN {$wpdb->postmeta} AS longitud_prof ON {$wpdb->posts}.ID = longitud_prof.post_id AND longitud_prof.meta_key = 'longitud_profesional'";

        // Añadir el campo distance
        $clauses['fields'] .= ", ( 6371 * acos( cos( radians({$this->user_lat}) ) * cos( radians( CAST(latitud_prof.meta_value AS DECIMAL(10,6)) ) ) * cos( radians( CAST(longitud_prof.meta_value AS DECIMAL(10,6)) ) - radians({$this->user_lng})) + sin( radians({$this->user_lat}) ) * sin( radians( CAST(latitud_prof.meta_value AS DECIMAL(10,6)) ) ) ) ) AS distance";

        // Añadir el ORDER BY
        $clauses['orderby'] = "distance ASC";

        // Añadir el HAVING
        $clauses['having'] = "distance < {$this->search_radius}";

        return $clauses;
    }
}

DP_Shortcodes::get_instance();