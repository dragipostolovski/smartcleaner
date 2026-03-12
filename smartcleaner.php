<?php
/**
 * Plugin Name: Smart Cleaner
 * Description: A powerful WordPress plugin designed to optimize your website's performance by identifying and removing orphaned postmeta entries, ensuring a cleaner and more efficient database.
 * Version: 1.0.1
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