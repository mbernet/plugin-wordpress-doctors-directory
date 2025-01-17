<?php
/*
Plugin Name: Directorio de Profesionales
Plugin URI: https://liquidbcn.com
Description: Crea un directorio de profesionales con filtrado por género y ubicación, mostrando la distancia desde la ubicación del usuario. Personaliza el radio de búsqueda y habilita la depuración.
Version: 2.0
Author: Marc Bernet
Author URI: https://liquidbcn.com
License: GPL2
Text Domain: directorio-profesionales
*/

if (!defined('ABSPATH')) {
    exit;
}

define('DP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DP_PLUGIN_VERSION', '1.0');

require_once DP_PLUGIN_DIR . 'includes/class-dp-cpt.php';
require_once DP_PLUGIN_DIR . 'includes/class-dp-acf-fields.php';
require_once DP_PLUGIN_DIR . 'includes/class-dp-geocoding.php';
require_once DP_PLUGIN_DIR . 'includes/class-dp-shortcodes.php';
require_once DP_PLUGIN_DIR . 'includes/class-dp-enqueue.php';
require_once DP_PLUGIN_DIR . 'includes/class-dp-settings.php';

function dp_init_plugin() {
    DP_CPT::get_instance();
    DP_ACF_Fields::get_instance();
    DP_Geocoding::get_instance();
    DP_Shortcodes::get_instance();
    DP_Enqueue::get_instance();
    DP_Settings::get_instance(); // Nueva línea

}
add_action('plugins_loaded', 'dp_init_plugin');