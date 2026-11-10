<?php
/**
 * Plugin Name:       Webcasata Elementor Addons
 * Plugin URI:        https://webcasata.com
 * Description:       Extra Elementor widgets in their own "Webcasata" category: Accordion, Feature Grid, Icon Scroll, Icon Box and Video (loaded only when played), plus a Content Filter bridge and Container / Parent-Hover styling extensions. Every widget can be switched on or off under Settings > Webcasata Addons, and only active widgets load CSS or JS.
 * Version:           1.11.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Author:            Webcasata
 * Author URI:        https://webcasata.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       webcasata-elementor-addons
 *
 * @package Webcasata_Elementor_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'QYRUS_ACCORDION_VERSION', '1.11.1' );
define( 'QYRUS_ACCORDION_FILE', __FILE__ );
define( 'QYRUS_ACCORDION_URL', plugin_dir_url( __FILE__ ) );
define( 'QYRUS_ACCORDION_PATH', plugin_dir_path( __FILE__ ) );
define( 'WEBCASATA_ADDONS_MIN_ELEMENTOR', '3.18.0' );

require_once QYRUS_ACCORDION_PATH . 'includes/class-webcasata-addons.php';

Webcasata_Addons::instance();
