<?php
/**
 * Plugin Name: Webcasata Elementor Addons
 * Description: Custom Elementor widgets built for the Qyrus site, grouped under their own "Webcasata" category in the widget panel. Includes Webcasata Accordion (left/right accordion where the image swaps per item, with its own mobile inline-image layout), Webcasata Feature Grid (icon + title + description cards in a responsive grid), Webcasata Icon Scroll (infinite vertical/horizontal auto-scrolling icon wall), and Webcasata Video (YouTube / Vimeo / self-hosted video that is only loaded when played: popup or inline), Webcasata Icon Box (icon with a styleable background, focused title, text fill/stroke/shadow, title separator, pseudo-elements), and Webcasata Content Filter (drops a saved Webcasata Content Filter plugin filter onto the page). Also extends Elementor's Container with :before / :after pseudo-element controls (Normal + Hover, no custom CSS), and adds a "Style On Parent Hover" section to every widget and container.
 * Version:     1.10.0
 * Author:      Webcasata
 * Author URI:  https://webcasata.com
 * Text Domain: webcasata-elementor-addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'QYRUS_ACCORDION_VERSION', '1.10.0' );
define( 'QYRUS_ACCORDION_URL', plugin_dir_url( __FILE__ ) );
define( 'QYRUS_ACCORDION_PATH', plugin_dir_path( __FILE__ ) );

final class Qyrus_Accordion_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	public function on_plugins_loaded() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
			return;
		}

		// Container extension: :before / :after controls on Elementor's own Container.
		require_once QYRUS_ACCORDION_PATH . 'includes/class-webcasata-pseudo-elements.php';
		// "Style On Parent Hover" for every widget and container (Advanced tab).
		require_once QYRUS_ACCORDION_PATH . 'includes/class-webcasata-parent-hover.php';

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );
	}

	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'webcasata',
			[
				'title' => __( 'Webcasata', 'webcasata-elementor-addons' ),
				'icon'  => 'eicon-apps',
			]
		);
	}

	public function admin_notice_missing_elementor() {
		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'Webcasata Elementor Addons requires Elementor to be installed and activated.', 'webcasata-elementor-addons' ) .
			'</p></div>';
	}

	public function register_widgets( $widgets_manager ) {
		require_once QYRUS_ACCORDION_PATH . 'widgets/class-qyrus-home-accordion-widget.php';
		require_once QYRUS_ACCORDION_PATH . 'widgets/class-qyrus-feature-grid-widget.php';
		require_once QYRUS_ACCORDION_PATH . 'widgets/class-qyrus-icon-marquee-widget.php';
		require_once QYRUS_ACCORDION_PATH . 'widgets/class-webcasata-icon-box-widget.php';
		require_once QYRUS_ACCORDION_PATH . 'widgets/class-webcasata-video-widget.php';

		$widgets_manager->register( new \Qyrus_Home_Accordion_Widget() );
		$widgets_manager->register( new \Qyrus_Feature_Grid_Widget() );
		$widgets_manager->register( new \Qyrus_Icon_Marquee_Widget() );
		$widgets_manager->register( new \Webcasata_Icon_Box_Widget() );
		$widgets_manager->register( new \Webcasata_Video_Widget() );

		// Soft dependency: only register this widget if the separate
		// "Webcasata Content Filter" plugin is active, since it wraps
		// that plugin's own WBCF_Render/WBCF_Assets classes rather than
		// duplicating any of their logic. (That plugin's internal
		// prefix is "WBCF_", not "WCF_" -- it was changed on their end
		// to avoid colliding with WooCommerce's "WC_" namespace.)
		if ( class_exists( 'WBCF_Render' ) ) {
			require_once QYRUS_ACCORDION_PATH . 'widgets/class-qyrus-wcf-filter-widget.php';
			$widgets_manager->register( new \Qyrus_WCF_Filter_Widget() );
		} else {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_wcf' ] );
		}
	}

	public function admin_notice_missing_wcf() {
		echo '<div class="notice notice-info"><p>' .
			esc_html__( 'Webcasata Elementor Addons: install and activate the "Webcasata Content Filter" plugin to unlock the Webcasata Content Filter widget.', 'webcasata-elementor-addons' ) .
			'</p></div>';
	}

	public function register_scripts() {
		wp_register_script(
			'qyrus-accordion',
			QYRUS_ACCORDION_URL . 'assets/js/qyrus-accordion.js',
			[ 'jquery', 'elementor-frontend' ],
			QYRUS_ACCORDION_VERSION,
			true
		);

		wp_register_script(
			'webcasata-video',
			QYRUS_ACCORDION_URL . 'assets/js/webcasata-video.js',
			[ 'jquery', 'elementor-frontend' ],
			QYRUS_ACCORDION_VERSION,
			true
		);
	}

	public function register_styles() {
		wp_register_style(
			'qyrus-accordion',
			QYRUS_ACCORDION_URL . 'assets/css/qyrus-accordion.css',
			[],
			QYRUS_ACCORDION_VERSION
		);

		wp_register_style(
			'qyrus-feature-grid',
			QYRUS_ACCORDION_URL . 'assets/css/qyrus-feature-grid.css',
			[],
			QYRUS_ACCORDION_VERSION
		);

		wp_register_style(
			'webcasata-icon-box',
			QYRUS_ACCORDION_URL . 'assets/css/webcasata-icon-box.css',
			[],
			QYRUS_ACCORDION_VERSION
		);

		wp_register_style(
			'webcasata-video',
			QYRUS_ACCORDION_URL . 'assets/css/webcasata-video.css',
			[],
			QYRUS_ACCORDION_VERSION
		);

		wp_register_style(
			'qyrus-icon-marquee',
			QYRUS_ACCORDION_URL . 'assets/css/qyrus-icon-marquee.css',
			[],
			QYRUS_ACCORDION_VERSION
		);
	}
}

Qyrus_Accordion_Plugin::instance();
