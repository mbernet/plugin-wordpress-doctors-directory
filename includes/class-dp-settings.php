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
        register_setting('dp_settings_group', 'dp_google_maps_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        add_settings_section(
            'dp_settings_section',
            __('Configuración de API de Google Maps', 'directorio-profesionales'),
            array($this, 'settings_section_callback'),
            'dp-settings'
        );

        add_settings_field(
            'dp_google_maps_api_key',
            __('Clave API de Google Maps', 'directorio-profesionales'),
            array($this, 'api_key_field_callback'),
            'dp-settings',
            'dp_settings_section'
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
        </div>
        <?php
    }

    public function settings_section_callback() {
        echo '<p>' . esc_html__('Introduce tu clave API de Google Maps para habilitar las funcionalidades de geocodificación.', 'directorio-profesionales') . '</p>';
    }

    public function api_key_field_callback() {
        $api_key = get_option('dp_google_maps_api_key');
        echo '<input type="text" id="dp_google_maps_api_key" name="dp_google_maps_api_key" value="' . esc_attr($api_key) . '" size="50" />';
    }
}