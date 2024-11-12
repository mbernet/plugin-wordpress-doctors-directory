<?php
// Archivo: includes/class-dp-acf-fields.php

if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class DP_ACF_Fields {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_ACF_Fields();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('acf/init', array($this, 'register_acf_fields'));
    }

    public function register_acf_fields() {
        if (function_exists('acf_add_local_field_group')) {

            acf_add_local_field_group(array(
                'key' => 'group_dp_profesionales',
                'title' => 'Datos del Profesional',
                'fields' => array(
                    array(
                        'key' => 'field_nombre_profesional',
                        'label' => 'Nombre',
                        'name' => 'nombre_profesional',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_centro_medico',
                        'label' => 'Centro Médico',
                        'name' => 'centro_medico',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_direccion_profesional',
                        'label' => 'Dirección',
                        'name' => 'direccion_profesional',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_poblacion_profesional',
                        'label' => 'Población',
                        'name' => 'poblacion_profesional',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_correo_profesional',
                        'label' => 'Correo',
                        'name' => 'correo_profesional',
                        'type' => 'email',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_telefono_profesional',
                        'label' => 'Teléfono',
                        'name' => 'telefono_profesional',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_latitud_profesional',
                        'label' => 'Latitud',
                        'name' => 'latitud_profesional',
                        'type' => 'number',
                        'instructions' => 'Latitud geocodificada automáticamente.',
                        'readonly' => 1,
                        'required' => 0,
                        'step' => '0.000001',
                    ),
                    array(
                        'key' => 'field_longitud_profesional',
                        'label' => 'Longitud',
                        'name' => 'longitud_profesional',
                        'type' => 'number',
                        'instructions' => 'Longitud geocodificada automáticamente.',
                        'readonly' => 1,
                        'required' => 0,
                        'step' => '0.000001',
                    ),
                    array(
                        'key' => 'field_biografia_profesional',
                        'label' => 'Biografía',
                        'name' => 'biografia_profesional',
                        'type' => 'textarea',
                        'required' => 0,
                    ),
                    array(
                        'key' => 'field_imagen_profesional',
                        'label' => 'Imagen',
                        'name' => 'imagen_profesional',
                        'type' => 'image',
                        'return_format' => 'array',
                        'preview_size' => 'thumbnail',
                        'library' => 'all',
                        'required' => 0,
                    ),
                    array(
                        'key' => 'field_logo_clinica_profesional',
                        'label' => 'Logo Clínica',
                        'name' => 'logo_clinica_profesional',
                        'type' => 'image',
                        'return_format' => 'array',
                        'preview_size' => 'thumbnail',
                        'library' => 'all',
                        'required' => 0,
                    ),
                    array(
                        'key' => 'field_link_profesional',
                        'label' => 'Link',
                        'name' => 'link_profesional',
                        'type' => 'url',
                        'required' => 0,
                    ),
                    array(
                        'key' => 'field_url_profesional',
                        'label' => 'URL',
                        'name' => 'url_profesional',
                        'type' => 'url',
                        'required' => 0,
                    ),
                    array(
                        'key' => 'field_genero_profesional',
                        'label' => 'Género',
                        'name' => 'genero_profesional',
                        'type' => 'select',
                        'choices' => array(
                            'Masculino' => 'Masculino',
                            'Femenino' => 'Femenino',
                            'Transgenero' => 'Transgénero',
                        ),
                        'default_value' => array(),
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'ajax' => 0,
                        'placeholder' => '',
                        'required' => 1,
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'profesional',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ));
        }
    }
}