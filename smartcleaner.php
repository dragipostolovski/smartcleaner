<?php
/**
 * Plugin Name: Smart Cleaner
 * Description: A powerful WordPress plugin designed to optimize your website's performance by identifying and removing orphaned postmeta entries, ensuring a cleaner and more efficient database.
 * Version: 1.0.3
 * Author: Dragi Postolovski
 * Author URI: https://dragipostolovski.com
 * Plugin URI: https://github.com/dragipostolovski/smartcleaner
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// ======================================
// INCLUDE CORE FUNCTIONS
// ======================================
require_once plugin_dir_path(__FILE__) . 'includes/orphaned-postmeta.php';

define('SC_ADMIN_MENU_SLUG', 'smartcleaner');
define('SC_ORPHANED_POSTMETA_MENU_SLUG', 'smartcleaner-orphaned-postmeta');
define('SC_ADMIN_MENU_ICON', plugin_dir_url(__FILE__) . 'build/src/icons/washing-machine.svg');

function sc_get_admin_menu_icon_data_uri($color) {
	$icon_path = plugin_dir_path(__FILE__) . 'build/src/icons/washing-machine.svg';
	$svg = file_get_contents($icon_path);

	if ($svg === false) {
		return '';
	}

	$svg = str_replace('#000', $color, $svg);
	$svg = preg_replace('/>\s+</', '><', trim($svg));

	if ($svg === null) {
		return '';
	}

	return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function sc_render_admin_dashboard_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>Smart Cleaner</h1>';
	echo '<p>Database cleanup tools for finding and removing orphaned WordPress data safely.</p>';
	echo '<ul style="list-style:disc;padding-left:20px;">';
	echo '<li><a href="' . esc_url(admin_url('admin.php?page=' . SC_ORPHANED_POSTMETA_MENU_SLUG)) . '">Orphaned Postmeta</a></li>';
	echo '</ul>';
	echo '</div>';
}

function sc_register_admin_menu() {
	add_menu_page(
		'Smart Cleaner',
		'Smart Cleaner',
		'manage_options',
		SC_ADMIN_MENU_SLUG,
		'sc_render_admin_dashboard_page',
		'',
		58
	);

	add_submenu_page(
		SC_ADMIN_MENU_SLUG,
		'Smart Cleaner',
		'Overview',
		'manage_options',
		SC_ADMIN_MENU_SLUG,
		'sc_render_admin_dashboard_page'
	);

	add_submenu_page(
		SC_ADMIN_MENU_SLUG,
		'Orphaned Postmeta',
		'Orphaned Postmeta',
		'manage_options',
		SC_ORPHANED_POSTMETA_MENU_SLUG,
		'sc_find_orphaned_postmeta_page'
	);
}
add_action('admin_menu', 'sc_register_admin_menu');

function sc_print_admin_menu_icon_styles() {
	$default_icon = esc_attr( sc_get_admin_menu_icon_data_uri( '#a7aaad' ) );
	$active_icon = esc_attr( sc_get_admin_menu_icon_data_uri( '#72aee6' ) );
	?>
	<style>
		#toplevel_page_<?php echo esc_html( SC_ADMIN_MENU_SLUG ); ?> .wp-menu-image:before {
			content: '';
			display: block;
			width: 20px;
			height: 20px;
			margin: 0 auto;
			background: url('<?php echo $default_icon; ?>') no-repeat center / 20px 20px;
		}

		#toplevel_page_<?php echo esc_html( SC_ADMIN_MENU_SLUG ); ?>:hover .wp-menu-image:before,
		#toplevel_page_<?php echo esc_html( SC_ADMIN_MENU_SLUG ); ?>.wp-has-current-submenu .wp-menu-image:before,
		#toplevel_page_<?php echo esc_html( SC_ADMIN_MENU_SLUG ); ?>.current .wp-menu-image:before {
			background-image: url('<?php echo $active_icon; ?>');
		}
	</style>
	<?php
}
add_action( 'admin_head', 'sc_print_admin_menu_icon_styles' );


// ======================================
// PLUGIN UPDATE CHECKER (GitHub Integration)
// ======================================
require 'vendor/autoload.php';
$myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/dragipostolovski/smartcleaner/',
	__FILE__,
	'smartcleaner'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');