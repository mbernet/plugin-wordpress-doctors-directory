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
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
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
        wp_enqueue_script(
            'directorio-profesionales-script',
            DP_PLUGIN_URL . 'assets/js/directorio-profesionales.js',
            array('jquery'),
            DP_PLUGIN_VERSION,
            true
        );
    }
}