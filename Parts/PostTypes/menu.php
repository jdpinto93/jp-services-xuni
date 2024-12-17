<?php

namespace Parts\PostTypes;

defined('ABSPATH') or die('No script kiddies please!');
class Menu
{

    private $post_type = 'menu';
    private $taxonomies = [
        'tipo',
        'promociones',
    ];

    public function __construct()
    {
        add_action('init', array($this, 'Menu'));
        add_action('restrict_manage_posts', array($this, 'add_filters_to_products'));
        add_filter('parse_query', array($this, 'filter_post_type_requests'));
    }

    public function Menu()
    {

        $labels = array(
            'name'                  => _x('Menu', 'Menu General Name', 'jp-services'),
            'singular_name'         => _x('Menu', 'Menu Singular Name', 'jp-services'),
            'menu_name'             => __('Menu', 'jp-services'),
            'name_admin_bar'        => __('Menu', 'jp-services'),
            'archives'              => __('Listado de Menu', 'jp-services'),
            'attributes'            => __('Atributos', 'jp-services'),
            'parent_item_colon'     => __('Menus Relacionados:', 'jp-services'),
            'all_items'             => __('Todos los Menus', 'jp-services'),
            'add_new_item'          => __('Añadir Nuevo Menu', 'jp-services'),
            'add_new'               => __('Añadir Nuevo', 'jp-services'),
            'new_item'              => __('Nuevo Menu', 'jp-services'),
            'edit_item'             => __('Editar Menu', 'jp-services'),
            'update_item'           => __('Actualizar Menu', 'jp-services'),
            'view_item'             => __('Ver Menu', 'jp-services'),
            'view_items'            => __('Ver Menu', 'jp-services'),
            'search_items'          => __('Buscar Menu', 'jp-services'),
            'not_found'             => __('No Encontrado', 'jp-services'),
            'not_found_in_trash'    => __('No Encontrado en papelera', 'jp-services'),
            'featured_image'        => __('Imagen Destacada', 'jp-services'),
            'set_featured_image'    => __('Establecer Imagen Destacada', 'jp-services'),
            'remove_featured_image' => __('Eliminar Imagen Destacada', 'jp-services'),
            'use_featured_image'    => __('Usar como Imagen Destacada', 'jp-services'),
            'insert_into_item'      => __('Insertar en Menu', 'jp-services'),
            'uploaded_to_this_item' => __('Subir a este Menu', 'jp-services'),
            'items_list'            => __('Lista de Menu', 'jp-services'),
            'items_list_navigation' => __('Navegar en lista de Menu', 'jp-services'),
            'filter_items_list'     => __('Filtrar Lista de Menu', 'jp-services'),
        );
        $args = array(
            'label'                 => __('Menu', 'jp-services'),
            'description'           => __('Menu Description', 'jp-services'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'page-attributes'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 56,
            'menu_icon'             => 'dashicons-welcome-write-blog',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        );

        register_post_type($this->post_type, $args);
    }

    public function add_filters_to_products()
    {
        global $typenow;
        $post_type = $this->post_type;
        $taxonomies = $this->taxonomies;

        if ($typenow == $post_type) {
            foreach ($taxonomies as $taxonomy) {
                $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
                $info_taxonomy = get_taxonomy($taxonomy);

                $query_args = [
                    'post_type' => $post_type,
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                    'tax_query' => [],
                ];

                foreach ($taxonomies as $other_taxonomy) {
                    if ($other_taxonomy != $taxonomy && isset($_GET[$other_taxonomy]) && !empty($_GET[$other_taxonomy])) {
                        $query_args['tax_query'][] = [
                            'taxonomy' => $other_taxonomy,
                            'field' => 'term_id',
                            'terms' => $_GET[$other_taxonomy],
                        ];
                    }
                }

                if (count($query_args['tax_query']) > 1) {
                    $query_args['tax_query']['relation'] = 'AND';
                }

                $posts_ids = get_posts($query_args);

                if (!empty($posts_ids)) {
                    $terms = wp_get_object_terms($posts_ids, $taxonomy, ['fields' => 'ids']);
                    $include_terms = !empty($terms) ? $terms : [];
                } else {
                    $include_terms = [];
                }

                if (empty($include_terms)) {

                    echo "<select name='{$taxonomy}' disabled>";
                    echo "<option value=''>" . __("No hay {$info_taxonomy->label} disponibles") . "</option>";
                    echo "</select>";
                } else {

                    wp_dropdown_categories([
                        'show_option_all' => __("Todas las {$info_taxonomy->label}"),
                        'taxonomy'        => $taxonomy,
                        'name'            => $taxonomy,
                        'orderby'         => 'name',
                        'selected'        => $selected,
                        'show_count'      => true,
                        'hide_empty'      => false,
                        'include'         => $include_terms,
                    ]);
                }
            }
        }
    }

    public function filter_post_type_requests($query)
    {
        global $pagenow;
        $post_type = $this->post_type;
        $taxonomies = $this->taxonomies;

        $q_vars = &$query->query_vars;

        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
            foreach ($taxonomies as $taxonomy) {
                if (isset($_GET[$taxonomy]) && is_numeric($_GET[$taxonomy]) && $_GET[$taxonomy] != 0) {
                    $term = get_term_by('term_id', $_GET[$taxonomy], $taxonomy);
                    $q_vars[$taxonomy] = $term->slug;
                }
            }
        }
    }
}

if (class_exists("Parts\PostTypes\Menu")) {
    new Menu();
}
