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
    private $radius = 50; // Radio en km

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_Shortcodes();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->api_key = get_option('dp_google_maps_api_key'); // Recupera la API Key desde las opciones del plugin
        add_shortcode('directorio_profesionales', array($this, 'directorio_profesionales_shortcode'));
    }

    public function directorio_profesionales_shortcode($atts) {
        global $wpdb;

        if (empty($this->api_key)) {
            echo '<p>La clave API de Google Maps no está configurada. Por favor, <a href="' . admin_url('options-general.php?page=dp-settings') . '">configúrala aquí</a>.</p>';
            return;
        }

        ob_start();

        // Obtener los parámetros de filtrado
        $genero  = isset($_GET['genero']) ? sanitize_text_field($_GET['genero']) : '';

        // Formularios para filtrar
        ?>
        <form method="GET" id="form-filtro-profesionales">
            <div>
                <label for="direccion_usuario">Tu Dirección:</label>
                <input type="text" id="direccion_usuario" name="direccion_usuario" placeholder="Ej: Calle Falsa 123, Ciudad" required>
            </div>
            <div>
                <label for="genero">Género:</label>
                <select id="genero" name="genero">
                    <option value="">Todos</option>
                    <option value="Masculino" <?php selected($genero, 'Masculino'); ?>>Masculino</option>
                    <option value="Femenino" <?php selected($genero, 'Femenino'); ?>>Femenino</option>
                    <option value="Otro" <?php selected($genero, 'Otro'); ?>>Otro</option>
                </select>
            </div>
            <button type="submit">Buscar</button>
            <div id="loading-spinner" style="display:none;">
                <img src="<?php echo esc_url(DP_PLUGIN_URL . 'assets/images/spinner.gif'); ?>" alt="Cargando..." />
            </div>
        </form>
        <?php

        // Si se ha enviado la dirección del usuario, geocodificarla
        if (isset($_GET['direccion_usuario'])) {
            $direccion_usuario = sanitize_text_field($_GET['direccion_usuario']);

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

        // Argumentos de la consulta
        $args = array(
            'post_type'      => 'profesional',
            'posts_per_page' => -1,
            'meta_query'     => array(),
        );

        // Filtrar por género si está seleccionado
        if (!empty($genero)) {
            $args['meta_query'][] = array(
                'key'     => 'genero_profesional',
                'value'   => $genero,
                'compare' => '=',
            );
        }

        // Si el usuario ha ingresado su ubicación, filtrar por proximidad
        if ($this->user_lat && $this->user_lng) {
            add_filter('posts_fields', array($this, 'add_distance_field'));
            add_filter('posts_join', array($this, 'add_distance_join'));
            add_filter('posts_orderby', array($this, 'order_by_distance'));
            add_filter('posts_clauses', array($this, 'add_distance_having'));

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

        // Realizar la consulta
        $query = new WP_Query($args);

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
                $distance = isset($post->distance) ? round($post->distance, 2) . ' km' : '';
                ?>
                <li class="profesional-item">
                    <?php if ($imagen): ?>
                        <img src="<?php echo esc_url($imagen['url']); ?>" alt="<?php echo esc_attr($imagen['alt']); ?>" width="100">
                    <?php endif; ?>
                    <div>
                        <h3><?php echo esc_html($nombre); ?></h3>
                        <?php if ($logo): ?>
                            <img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>" width="50">
                        <?php endif; ?>
                        <p><strong>Centro Médico:</strong> <?php echo esc_html($centro); ?></p>
                        <p><strong>Dirección:</strong> <?php echo esc_html($direccion . ', ' . $poblacion); ?></p>
                        <p><strong>Correo:</strong> <a href="mailto:<?php echo esc_attr($correo); ?>"><?php echo esc_html($correo); ?></a></p>
                        <p><strong>Teléfono:</strong> <a href="tel:<?php echo esc_attr($telefono); ?>"><?php echo esc_html($telefono); ?></a></p>
                        <p><strong>Género:</strong> <?php echo esc_html($genero); ?></p>
                        <p><?php echo esc_html($biografia); ?></p>
                        <?php if ($link): ?>
                            <p><a href="<?php echo esc_url($link); ?>" target="_blank">Perfil</a></p>
                        <?php endif; ?>
                        <?php if ($url): ?>
                            <p><a href="<?php echo esc_url($url); ?>" target="_blank">Sitio Web</a></p>
                        <?php endif; ?>
                        <?php if ($distance): ?>
                            <p><strong>Distancia:</strong> <?php echo esc_html($distance); ?></p>
                        <?php endif; ?>
                    </div>
                </li>
                <?php
            }
            echo '</ul>';
            wp_reset_postdata();
        } else {
            echo '<p>No se encontraron profesionales que coincidan con los criterios de búsqueda.</p>';
        }

        if ($this->user_lat && $this->user_lng) {
            remove_filter('posts_fields', array($this, 'add_distance_field'));
            remove_filter('posts_join', array($this, 'add_distance_join'));
            remove_filter('posts_orderby', array($this, 'order_by_distance'));
            remove_filter('posts_clauses', array($this, 'add_distance_having'));
        }

        // Div de depuración
        ?>
        <div id="dp-debug" style="background-color: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin-top: 20px;">
            <h4>Debugging Information</h4>
            <p><strong>Dirección del Usuario:</strong> <?php echo isset($direccion_usuario) ? esc_html($direccion_usuario) : 'N/A'; ?></p>
            <p><strong>Latitud del Usuario:</strong> <?php echo esc_html($this->user_lat); ?></p>
            <p><strong>Longitud del Usuario:</strong> <?php echo esc_html($this->user_lng); ?></p>
            <p><strong>Género Seleccionado:</strong> <?php echo esc_html($genero); ?></p>
            <p><strong>Consulta SQL:</strong> <?php echo isset($query) ? esc_html($query->request) : 'N/A'; ?></p>
        </div>
        <?php

        return ob_get_clean();
    }

    public function add_distance_field($fields) {
        return $fields . ", ( 6371 * acos( cos( radians({$this->user_lat}) ) * cos( radians( CAST(latitud_prof.meta_value AS DECIMAL(10,6)) ) ) * cos( radians( CAST(longitud_prof.meta_value AS DECIMAL(10,6)) ) - radians({$this->user_lng})) + sin( radians({$this->user_lat}) ) * sin( radians( CAST(latitud_prof.meta_value AS DECIMAL(10,6)) ) ) ) ) AS distance";
    }

    public function add_distance_join($join) {
        global $wpdb;
        $join .= " INNER JOIN {$wpdb->postmeta} AS latitud_prof ON {$wpdb->posts}.ID = latitud_prof.post_id AND latitud_prof.meta_key = 'latitud_profesional'";
        $join .= " INNER JOIN {$wpdb->postmeta} AS longitud_prof ON {$wpdb->posts}.ID = longitud_prof.post_id AND longitud_prof.meta_key = 'longitud_profesional'";
        return $join;
    }

    public function order_by_distance($orderby) {
        return "distance ASC";
    }

    public function add_distance_having($clauses) {
        global $wpdb;
        $clauses['having'] = "distance < {$this->radius}";
        return $clauses;
    }
}