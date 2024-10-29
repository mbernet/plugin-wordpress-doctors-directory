<?php
// Archivo: includes/class-dp-cpt.php

if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo
}

class DP_CPT {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new DP_CPT();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'register_cpt_profesional'));
        add_action('init', array($this, 'dp_register_profesional_post_type'));
    }
    function dp_register_profesional_post_type() {
        $args = array(
            'public' => true,
            'label'  => 'Profesionales',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'revisions'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'profesionales'),
            'show_in_rest' => true,
        );
        register_post_type('profesional', $args);
    }


    public function register_cpt_profesional() {
        $labels = array(
            'name'                  => _x('Profesionales', 'Post Type General Name', 'directorio-profesionales'),
            'singular_name'         => _x('Profesional', 'Post Type Singular Name', 'directorio-profesionales'),
            'menu_name'             => __('Profesionales', 'directorio-profesionales'),
            'name_admin_bar'        => __('Profesional', 'directorio-profesionales'),
            'archives'              => __('Archivo de Profesionales', 'directorio-profesionales'),
            'attributes'            => __('Atributos', 'directorio-profesionales'),
            'parent_item_colon'     => __('Profesional Padre:', 'directorio-profesionales'),
            'all_items'             => __('Todos los Profesionales', 'directorio-profesionales'),
            'add_new_item'          => __('Añadir Nuevo Profesional', 'directorio-profesionales'),
            'add_new'               => __('Añadir Nuevo', 'directorio-profesionales'),
            'new_item'              => __('Nuevo Profesional', 'directorio-profesionales'),
            'edit_item'             => __('Editar Profesional', 'directorio-profesionales'),
            'update_item'           => __('Actualizar Profesional', 'directorio-profesionales'),
            'view_item'             => __('Ver Profesional', 'directorio-profesionales'),
            'view_items'            => __('Ver Profesionales', 'directorio-profesionales'),
            'search_items'          => __('Buscar Profesional', 'directorio-profesionales'),
            'not_found'             => __('No se encontraron profesionales', 'directorio-profesionales'),
            'not_found_in_trash'    => __('No se encontraron profesionales en la papelera', 'directorio-profesionales'),
            'featured_image'        => __('Imagen Destacada', 'directorio-profesionales'),
            'set_featured_image'    => __('Establecer imagen destacada', 'directorio-profesionales'),
            'remove_featured_image' => __('Eliminar imagen destacada', 'directorio-profesionales'),
            'use_featured_image'    => __('Usar como imagen destacada', 'directorio-profesionales'),
            'insert_into_item'      => __('Insertar en profesional', 'directorio-profesionales'),
            'uploaded_to_this_item' => __('Subido a este profesional', 'directorio-profesionales'),
            'items_list'            => __('Lista de profesionales', 'directorio-profesionales'),
            'items_list_navigation' => __('Navegación de la lista', 'directorio-profesionales'),
            'filter_items_list'     => __('Filtrar la lista', 'directorio-profesionales'),
        );
        $args = array(
            'label'                 => __('Profesional', 'directorio-profesionales'),
            'description'           => __('Directorio de Profesionales', 'directorio-profesionales'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-businessperson',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type('profesional', $args);
    }
}