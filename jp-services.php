<?php

/**
 * Plugin Name:     Jp Services
 * Plugin URI:      https://webmasteryagency.com/
 * Description:     Servicios personalizados por Jose Pinto
 * Author:          Jose Pinto
 * Author URI:      https://webmasteryagency.com/
 * Text Domain:     jp-services
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Jp_Services
 */

/**
 * Constants for the plugin
 */
define('JP_SERVICES_VERSION', '1.0.0');
define('JP_SERVICES_URL', plugin_dir_url(__FILE__));
define('JP_SERVICES_PATH', plugin_dir_path(__FILE__));

/**
 * Include Router
 */
include_once JP_SERVICES_PATH . '/Router/Route.php';