<?php
namespace Parts\Taxonomies;

defined('ABSPATH') or die('No script kiddies please!');

class Promociones {

    public function __construct() {
        add_action('init', array($this, 'register_taxonomy'));
    }

    public function register_taxonomy() {
        $labels = array(
            'name'                       => _x('Promociones', 'Taxonomy General Name', 'jp-services'),
            'singular_name'              => _x('Promociones', 'Taxonomy Singular Name', 'jp-services'),
            'menu_name'                  => __('Promociones', 'jp-services'),
            'all_items'                  => __('Todos los Promociones', 'jp-services'),
            'parent_item'                => __('Promociones Relacionados', 'jp-services'),
            'parent_item_colon'          => __('Promociones Relacionados:', 'jp-services'),
            'new_item_name'              => __('Nuevo Promociones', 'jp-services'),
            'add_new_item'               => __('Añadir Nuevo Promociones', 'jp-services'),
            'edit_item'                  => __('Editar Promociones', 'jp-services'),
            'update_item'                => __('Actualizar Promociones', 'jp-services'),
            'view_item'                  => __('Ver Promociones', 'jp-services'),
            'separate_items_with_commas' => __('Separar Promociones por coma', 'jp-services'),
            'add_or_remove_items'        => __('Añadir o Eliminar Promociones', 'jp-services'),
            'choose_from_most_used'      => __('Buscar por Promociones mas utilizados', 'jp-services'),
            'popular_items'              => __('Promociones Destacados', 'jp-services'),
            'search_items'               => __('Buscar Promociones', 'jp-services'),
            'not_found'                  => __('No Encontrado', 'jp-services'),
            'no_terms'                   => __('Sin Promociones', 'jp-services'),
            'items_list'                 => __('Lista de Promociones', 'jp-services'),
            'items_list_navigation'      => __('Navegar en lista de Promociones', 'jp-services'),
        );

        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        );

        register_taxonomy('promociones', array('menu'), $args);
    }
}

if (class_exists('Parts\Taxonomies\Promociones')) {
    new Promociones();
}