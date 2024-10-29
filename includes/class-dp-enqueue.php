<?php
// Archivo: includes/class-dp-enqueue.php

if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class DP_Enqueue {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_Enqueue();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 20);
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'directorio-profesionales-style',
            DP_PLUGIN_URL . 'assets/css/directorio-profesionales.css',
            array(),
            DP_PLUGIN_VERSION
        );
    }

    public function enqueue_scripts() {
        error_log('Enqueue scripts ejecutado');
        wp_register_script(
            'directorio-profesionales-script',
            DP_PLUGIN_URL . 'assets/js/directorio-profesionales.js',
            array('jquery'),
            DP_PLUGIN_VERSION,
            true
        );
        wp_enqueue_script(
            'directorio-profesionales-script',
            DP_PLUGIN_URL . 'assets/js/directorio-profesionales.js',
            array('jquery'),
            DP_PLUGIN_VERSION,
            true
        );

        $api_key = get_option('dp_google_maps_api_key');

        if (!empty($api_key)) {
            wp_enqueue_script('google-maps-places', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places', array(), null, true);
            wp_enqueue_script('dp-autocomplete', DP_PLUGIN_URL . 'assets/js/autocomplete.js', array('google-maps-places', 'jquery'), DP_PLUGIN_VERSION, true);
            wp_localize_script('dp-autocomplete', 'dp_ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                // Añade más datos si es necesario
            ));
        }
    }
}