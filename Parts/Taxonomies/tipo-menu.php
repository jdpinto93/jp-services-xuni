<?php
namespace Parts\Taxonomies;

defined('ABSPATH') or die('No script kiddies please!');

class Tipo {

    public function __construct() {
        add_action('init', array($this, 'register_taxonomy'));
    }

    public function register_taxonomy() {
        $labels = array(
            'name'                       => _x('Tipo', 'Taxonomy General Name', 'jp-services'),
            'singular_name'              => _x('Tipo', 'Taxonomy Singular Name', 'jp-services'),
            'menu_name'                  => __('Tipo', 'jp-services'),
            'all_items'                  => __('Todos los Tipo', 'jp-services'),
            'parent_item'                => __('Tipo Relacionados', 'jp-services'),
            'parent_item_colon'          => __('Tipo Relacionados:', 'jp-services'),
            'new_item_name'              => __('Nuevo Tipo', 'jp-services'),
            'add_new_item'               => __('Añadir Nuevo Tipo', 'jp-services'),
            'edit_item'                  => __('Editar Tipo', 'jp-services'),
            'update_item'                => __('Actualizar Tipo', 'jp-services'),
            'view_item'                  => __('Ver Tipo', 'jp-services'),
            'separate_items_with_commas' => __('Separar Tipo por coma', 'jp-services'),
            'add_or_remove_items'        => __('Añadir o Eliminar Tipo', 'jp-services'),
            'choose_from_most_used'      => __('Buscar por Tipo mas utilizados', 'jp-services'),
            'popular_items'              => __('Tipo Destacados', 'jp-services'),
            'search_items'               => __('Buscar Tipo', 'jp-services'),
            'not_found'                  => __('No Encontrado', 'jp-services'),
            'no_terms'                   => __('Sin Tipo', 'jp-services'),
            'items_list'                 => __('Lista de Tipo', 'jp-services'),
            'items_list_navigation'      => __('Navegar en lista de Tipo', 'jp-services'),
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

        register_taxonomy('tipo', array('menu'), $args);
    }
}

if (class_exists('Parts\Taxonomies\Tipo')) {
    new Tipo();
}