<?php
/**
 * UBC H5P Addon - Adhoc Group
 *
 * @package     UBC H5P
 * @author      Kelvin Xu
 * @copyright   2021 University of British Columbia
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: UBC H5P Addon - Adhoc Group
 * Plugin URI:  https://ubc.ca/
 * Description: The plugin allows administrators and editors to create adhoc groups and add users to individual group.
 * Version:     1.0.2
 * Author:      Kelvin Xu
 * Text Domain: ubc-h5p-adhoc-group
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace UBC\H5P\AdhocGroup;

define( 'H5P_ADHOCGROUP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'H5P_ADHOCGROUP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin initialization
 *
 * @return void
 */
function init() {
	if ( ! is_admin() ) {
		return;
	}

	if ( ! class_exists( '\UBC\H5P\Taxonomy\ContentTaxonomy\ContentTaxonomy' ) ) {
		return;
	}

	require_once 'includes/class-adhocgroup.php';
}

add_action( 'plugin_loaded', __NAMESPACE__ . '\\init' );
