<?php
/**
 * Plugin Name: Smart Cleaner
 * Description: A powerful WordPress plugin designed to optimize your website's performance by identifying and removing orphaned postmeta entries, ensuring a cleaner and more efficient database.
 * Version: 1.0.2
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
		'dashicons-database',
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