<?php
if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class DP_Geocoding {
    private static $instance = null;

    private function __construct() {
        add_action('save_post', array($this, 'geocode_profesional_address'), 20, 2);
    }

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_Geocoding();
        }
        return self::$instance;
    }

    public function geocode_profesional_address($post_id, $post) {
        // Verifica el tipo de post
        if ($post->post_type !== 'profesional') {
            return;
        }

        // Verifica si el post está en auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verifica permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Obtiene la dirección completa
        $direccion = get_field('direccion_profesional', $post_id);
        $poblacion = get_field('poblacion_profesional', $post_id);
        $direccion_completa = $direccion . ', ' . $poblacion;

        if (!$direccion_completa) {
            return;
        }

        // Obtener la API Key desde las opciones del plugin
        $api_key = get_option('dp_google_maps_api_key');
        if (empty($api_key)) {
            // Opcional: Puedes notificar al administrador que la clave API no está configurada
            error_log('DP_Geocoding: Clave API de Google Maps no está configurada.');
            return;
        }

        // Verifica si ya tiene latitud y longitud
        $lat = get_field('latitud_profesional', $post_id);
        $lng = get_field('longitud_profesional', $post_id);
        if ($lat && $lng) {
            return;
        }

        // URL de la API de Geocoding
        $url = add_query_arg(array(
            'address' => urlencode($direccion_completa),
            'key'     => $api_key,
        ), 'https://maps.googleapis.com/maps/api/geocode/json');

        // Realiza la solicitud a la API
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log('DP_Geocoding: Error en la solicitud a la API - ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ($data->status === 'OK') {
            $latitud = $data->results[0]->geometry->location->lat;
            $longitud = $data->results[0]->geometry->location->lng;

            // Actualiza los campos ACF
            update_field('latitud_profesional', $latitud, $post_id);
            update_field('longitud_profesional', $longitud, $post_id);
        } else {
            // Registra el error de geocodificación
            error_log('DP_Geocoding: Geocoding API error - Status: ' . $data->status . ' - ' . (isset($data->error_message) ? $data->error_message : ''));
        }
    }
}