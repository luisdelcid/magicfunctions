<?php
/*
Author: Luis del Cid
Author URI: https://luisdelcid.com/
Description: A collection of magic functions for WordPress, plugins and themes.
Domain Path:
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network: true
Plugin Name: Magic Functions
Plugin URI: https://magicfunctions.com/
Requires at least: 5.6
Requires PHP: 5.6
Text Domain: magicfunctions
Version: 0.1.21
*/

// Make sure we don't expose any info if called directly.
defined('ABSPATH') or die('Hi there! I\'m just a plugin, not much I can do when called directly.');

// Load PHP functions.
foreach(glob(plugin_dir_path(__FILE__) . 'autoload/php/*.php') as $magic_file){
    require_once($magic_file);
}
unset($magic_file);

// Check for updates.
__build_update_checker('https://github.com/luisdelcid/magicfunctions', __FILE__);

// Load JavaScript functions.
__enqueue_functions();

// Load theme functions.
__require_theme_functions();

/**
 * Fires when magic is fully loaded.
 */
do_action('magic_loaded');
