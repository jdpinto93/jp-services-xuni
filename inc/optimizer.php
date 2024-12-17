<?php
defined('ABSPATH') or die("Bye bye");
/**
 * Jp Services Optimizer
 *
 * @link https://developer.wordpress.org/themes/
 *
 * @package Jp_Services
 */

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('atom_head', 'the_generator');
remove_action('comments_atom_head', 'the_generator');
remove_action('rss_head', 'the_generator');
remove_action('rss2_head', 'the_generator');
remove_action('commentsrss2_head', 'the_generator');
remove_action('rdf_header', 'the_generator');
remove_action('opml_head', 'the_generator');
remove_action('app_head', 'the_generator');
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
remove_action('wp_head', 'wp_generator');

add_filter(
	'style_loader_src',
	function ($src) {
		if (strpos($src, 'ver=')) {
			$src = remove_query_arg('ver', $src);
		}
		return $src;
	},
	9999
);
add_filter(
	'script_loader_src',
	function ($src) {
		if (strpos($src, 'ver=')) {
			$src = remove_query_arg('ver', $src);
		}
		return $src;
	},
	9999
);

add_filter('show_recent_comments_widget_style', '__return_false');
remove_action('wp_head', 'wp_resource_hints', 2);
add_filter('login_display_language_dropdown', '__return_false');
if (is_admin()) {
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter(
		'tiny_mce_plugins',
		function ($plugins) {
			if (is_array($plugins)) {
				return array_diff($plugins, array('wpemoji'));
			}
			return array();
		}
	);
} else {
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
}

if (defined('WP_POST_REVISIONS') && (WP_POST_REVISIONS !== false)) {
	add_filter(
		'wp_revisions_to_keep',
		function ($num, $post) {
			return 5;
		},
		10,
		2
	);
}

add_filter(
	'heartbeat_settings',
	function ($settings) {
		$settings['interval'] = 60;
		return $settings;
	}
);

add_action(
	'wp_print_scripts',
	function () {
		if (is_singular() && (get_option('thread_comments') === 1) && comments_open() && have_comments()) {
			wp_enqueue_script('comment-reply');
		} else {
			wp_dequeue_script('comment-reply');
		}
	},
	100
);

if (!defined('EMPTY_TRASH_DAYS')) {
	define('EMPTY_TRASH_DAYS', 7);
}

foreach (array('the_content', 'the_title', 'wp_title', 'comment_text') as $machete_filter) {
	$machete_priority = has_filter($machete_filter, 'capital_P_dangit');
	if (false !== $machete_priority) {
		remove_filter($machete_filter, 'capital_P_dangit', $machete_priority);
	}
}

if (!defined('DISALLOW_FILE_EDIT')) {
	define('DISALLOW_FILE_EDIT', true);
}

remove_filter('comment_text', 'make_clickable', 9);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_style('wp-block-library-theme');
		wp_dequeue_style('wc-blocks-style');
		wp_dequeue_style('classic-theme-styles');
		wp_dequeue_style('woocommerce-layout');
		wp_dequeue_style('woocommerce-smallscreen');
		wp_dequeue_style('woocommerce-general');
		wp_dequeue_style('woocommerce-inline');
	},
	100000000
);
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

function delete_images_sizes($sizes)
{

	unset($sizes['thumbnail']);
	unset($sizes['medium']);
	unset($sizes['medium_large']);
	unset($sizes['large']);

	unset($sizes['shop_thumbnail']);
	unset($sizes['shop_catalog']);
	unset($sizes['shop_single']);
}
add_filter('intermediate_image_sizes_advanced', 'delete_images_sizes');

function remove_footer_admin()
{
	echo '<span id="footer-thankyou">Derechos Reservados <a href="' . network_site_url('/') . '" target="_blank">' . get_bloginfo('name') . '</a></span>';
	echo '<br><span id="footer-thankyou">Desarrollado por <a href="https://github.com/jdpinto93/" target="_blank">Jose Pinto</a></span>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

function jp_admin_bar_remove_logo()
{
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', 'jp_admin_bar_remove_logo', 0);

function redirect_404_to_home()
{
	if (is_404()) {
		wp_redirect(home_url(), 301);
		exit();
	}
}
add_action('template_redirect', 'redirect_404_to_home');

function jp_services_remove_menus()
{
	remove_menu_page('edit.php');                 //Entradas
	remove_menu_page('edit-comments.php');        //Comentarios
	remove_menu_page('upload.php');               //Media
	remove_menu_page('themes.php');               //Appearance
	remove_menu_page('plugins.php');              //Plugins
	remove_menu_page('tools.php');                //Herramientas
	remove_menu_page('options-general.php');      //Ajustes
}
add_action('admin_menu', 'jp_services_remove_menus');

function jp_services_remove_menus_with_addmin_styles()
{
	$output = <<<HTML
	 <style>
		#toplevel_page_wp-mail-smtp,
		#toplevel_page_jet-dashboard,
		#toplevel_page_jet-smart-filters,
		.separator-croco--plugins-before {
			display: none;
		}
	</style>
	HTML;
	echo $output;
}
add_action('admin_head', 'jp_services_remove_menus_with_addmin_styles');