<?php
/**
 * Plugin Name: CAH Committee Custom Post Type
 * Description: A custom post type to track committees and committee members in the UCF College of Arts and Humanities
 * Author: Mike W. Leavitt
 * Version: 1.0.0
 *
 * PHP Version 7
 *
 * @category UCF\CAH
 * @package  WordPress\CPT
 * @author   Mike W. Leavitt <michael.leavitt@ucf.edu>
 * @license  GNU General Public License, v3.0 <https://opensource.org/licenses/GPL-3>
 * @version  SVN: 1.0.0
 * @link     https://cah.ucf.edu/about/administration/committtees
 */
declare(strict_types = 1);

defined('ABSPATH') || die('No direct access plzthx');

// Define useful plugin constants
define('COMMITTEES__PLUGIN_FILE', __FILE__);
define('COMMITTEES__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COMMITTEES__PLUGIN_URI', plugin_dir_url(__FILE__));

// Make sure we have our parent class definitions in place
if (!class_exists("UCF\\CAH\\Lib\\WordPress\\WPCustomPostType")) {
    require_once "lib/class-abstract-wp-cpt.php";
}

if (!interface_exists("UCF\\CAH\\Lib\\WordPress\\WPCustomFieldsInterface")) {
    require_once "lib/interface-wp-cpt-custom-fields.php";
}

// Hook all our relevant actions in to get things rolling
require_once "includes/cah-committees-cpt-steup.php";
$cptNamespace = "UCF\\CAH\\WordPress\\CPT";
add_action('init', ["$cptNamespace\\Committee", "register"], 10, 0);
add_action('init', ["$cptNamespace\\Committee", "setupCustomFields"], 10, 0);
add_action('admin_enqueue_scripts', ["$cptNamespace\\Committee", "enqueueAdminScripts"]);
add_action('wp_enqueue_scripts', ["$cptNamespace\\Committee", "enqueueCommitteesScript"], 10, 0);
add_action('template_include', ["$cptNamespace\\Committee", "pageTemplate"]);
register_activation_hook(__FILE__, ["$cptNamespace\\Committee", "activate"]);
register_deactivation_hook(__FILE__, ["$cptNamespace\\Committee", "deactivate"]);
