<?php
if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class DP_Settings {
    private static $instance = null;
    private $options;

    private function __construct() {
        // Registrar ajustes
        add_action('admin_init', array($this, 'register_settings'));
        // Añadir menú
        add_action('admin_menu', array($this, 'add_settings_page'));
    }

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_Settings();
        }
        return self::$instance;
    }

    public function register_settings() {
        // Registrar Clave API de Google Maps
        register_setting('dp_settings_group', 'dp_google_maps_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        // Registrar Radio de Búsqueda
        register_setting('dp_settings_group', 'dp_search_radius', array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 50, // Valor por defecto: 50 km
        ));

        // Registrar Modo de Depuración
        register_setting('dp_settings_group', 'dp_debug_mode', array(
            'type' => 'boolean',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => false,
        ));

        // Añadir Sección de API Key
        add_settings_section(
            'dp_api_settings_section',
            __('Configuración de API de Google Maps', 'directorio-profesionales'),
            array($this, 'api_settings_section_callback'),
            'dp-settings'
        );

        // Añadir Campo de API Key
        add_settings_field(
            'dp_google_maps_api_key',
            __('Clave API de Google Maps', 'directorio-profesionales'),
            array($this, 'api_key_field_callback'),
            'dp-settings',
            'dp_api_settings_section'
        );

        // Añadir Sección de Configuración Adicional
        add_settings_section(
            'dp_additional_settings_section',
            __('Configuración Adicional', 'directorio-profesionales'),
            array($this, 'additional_settings_section_callback'),
            'dp-settings'
        );

        // Añadir Campo de Radio de Búsqueda
        add_settings_field(
            'dp_search_radius',
            __('Radio de Búsqueda (km)', 'directorio-profesionales'),
            array($this, 'search_radius_field_callback'),
            'dp-settings',
            'dp_additional_settings_section'
        );

        // Añadir Campo de Modo de Depuración
        add_settings_field(
            'dp_debug_mode',
            __('Modo de Depuración', 'directorio-profesionales'),
            array($this, 'debug_mode_field_callback'),
            'dp-settings',
            'dp_additional_settings_section'
        );


    }

    public function add_settings_page() {
        add_options_page(
            __('Directorio de Profesionales', 'directorio-profesionales'),
            __('Directorio de Profesionales', 'directorio-profesionales'),
            'manage_options',
            'dp-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('dp_settings_group');
                do_settings_sections('dp-settings');
                submit_button();
                ?>
            </form>
            <?php
            $this->display_instructions();
            ?>
            <?php $this->display_disclaimer(); ?>
        </div>
        <?php
    }

    public function api_settings_section_callback() {
        echo '<p>' . esc_html__('Introduce tu clave API de Google Maps para habilitar las funcionalidades de geocodificación.', 'directorio-profesionales') . '</p>';
    }

    public function api_key_field_callback() {
        $api_key = get_option('dp_google_maps_api_key');
        echo '<input type="text" id="dp_google_maps_api_key" name="dp_google_maps_api_key" value="' . esc_attr($api_key) . '" size="50" />';
    }

    public function additional_settings_section_callback() {
        echo '<p>' . esc_html__('Configura opciones adicionales para el directorio.', 'directorio-profesionales') . '</p>';
    }

    public function search_radius_field_callback() {
        $radius = get_option('dp_search_radius', 50);
        echo '<input type="number" id="dp_search_radius" name="dp_search_radius" value="' . esc_attr($radius) . '" min="1" max="500" /> ' . esc_html__('km', 'directorio-profesionales');
    }

    public function debug_mode_field_callback() {
        $debug = get_option('dp_debug_mode', false);
        echo '<input type="checkbox" id="dp_debug_mode" name="dp_debug_mode" value="1" ' . checked(1, $debug, false) . ' />';
        echo '<label for="dp_debug_mode"> ' . esc_html__('Habilitar modo de depuración para mostrar información adicional en el frontend.', 'directorio-profesionales') . '</label>';
    }

    public function display_instructions() {
        ?>
        <div id="dp-instructions" style="background-color: #f1f1f1; padding: 15px; border: 1px solid #ddd; margin-top: 20px;">
            <h2><?php echo esc_html__('Instrucciones de Uso', 'directorio-profesionales'); ?></h2>
            <ol>
                <li><?php echo esc_html__('Ingresa tu Clave API de Google Maps en la sección de configuración.', 'directorio-profesionales'); ?></li>
                <li><?php echo esc_html__('Configura el Radio de Búsqueda según tus necesidades.', 'directorio-profesionales'); ?></li>
                <li><?php echo esc_html__('Activa el Modo de Depuración si deseas ver información adicional en el frontend.', 'directorio-profesionales'); ?></li>
                <li><?php echo esc_html__('Utiliza el shortcode [directorio_profesionales] en cualquier página o entrada para mostrar el directorio.', 'directorio-profesionales'); ?></li>
                <li><?php echo esc_html__('Diseña las páginas individuales de los profesionales usando Elementor.', 'directorio-profesionales'); ?></li>
            </ol>
        </div>
        <?php
    }

    public function display_disclaimer() {
        ?>
        <div id="dp-disclaimer" style="background-color: #e8f4ff; padding: 15px; border: 1px solid #b3d7ff; margin-top: 20px;">
            <blockquote style="margin: 0; font-style: italic;">
                Just the two of us
                We can make it if we try
                Just the two of us
                (Just the two of us)
                Just the two of us
                Building castles in the sky
                Just the two of us
                You and I
            </blockquote>
        </div>
        <?php
    }

    public function sanitize_checkbox($input) {
        return ($input == 1) ? true : false;
    }
}

DP_Settings::get_instance();