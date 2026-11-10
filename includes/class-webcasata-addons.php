<?php
/**
 * Plugin loader: decides, from the settings, which widgets, extensions and
 * assets exist on this request.
 *
 * @package Webcasata_Elementor_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Loader.
 */
final class Webcasata_Addons {

	/**
	 * Single instance.
	 *
	 * @var Webcasata_Addons|null
	 */
	private static $instance = null;

	/**
	 * Settings + registry.
	 *
	 * @var Webcasata_Settings
	 */
	private $settings;

	/**
	 * Get the single instance.
	 *
	 * @return Webcasata_Addons
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook up. The settings page is always available, so the plugin can be
	 * switched back on even when it is currently off.
	 */
	private function __construct() {
		require_once QYRUS_ACCORDION_PATH . 'includes/class-webcasata-settings.php';

		$this->settings = Webcasata_Settings::instance();

		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	/**
	 * Elementor is only guaranteed to be loaded from plugins_loaded onwards.
	 */
	public function on_plugins_loaded() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, WEBCASATA_ADDONS_MIN_ELEMENTOR, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_old_elementor' ] );
			return;
		}

		// Master switch off: nothing is registered and nothing is enqueued.
		if ( ! $this->settings->is_master_enabled() ) {
			return;
		}

		$this->load_extensions();

		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );
	}

	/* ------------------------------------------------------------------
	 * Extensions
	 * ------------------------------------------------------------------ */

	/**
	 * Load the editor extensions that are switched on.
	 */
	private function load_extensions() {
		$pseudo_container = $this->settings->is_extension_enabled( 'pseudo-elements' );
		$parent_hover     = $this->settings->is_extension_enabled( 'parent-hover' );
		$icon_box         = $this->settings->is_widget_enabled( 'icon-box' );

		// The Icon Box reuses the pseudo-element controls, so the class is
		// loaded for it too; the Container controls only when the extension is on.
		if ( $pseudo_container || $icon_box ) {
			require_once QYRUS_ACCORDION_PATH . 'includes/class-webcasata-pseudo-elements.php';
			Webcasata_Pseudo_Elements::boot( $pseudo_container );
		}

		if ( $parent_hover ) {
			require_once QYRUS_ACCORDION_PATH . 'includes/class-webcasata-parent-hover.php';
			Webcasata_Parent_Hover::instance();
		}

		// One tiny shared script drives the "Custom class" hover triggers of
		// all three features. It is only added when one of them is on.
		if ( $pseudo_container || $parent_hover || $icon_box ) {
			add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_hover_script' ] );
			add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_hover_script' ] );
		}
	}

	/**
	 * Register the shared hover-trigger script.
	 */
	public function register_hover_script() {
		wp_register_script(
			'webcasata-pseudo-hover',
			QYRUS_ACCORDION_URL . 'assets/js/webcasata-pseudo-hover.js',
			[ 'jquery', 'elementor-frontend' ],
			QYRUS_ACCORDION_VERSION,
			true
		);
	}

	/**
	 * Enqueue it. Kept global (rather than per element) because Elementor can
	 * serve cached element markup that skips the before_render hooks.
	 */
	public function enqueue_hover_script() {
		wp_enqueue_script( 'webcasata-pseudo-hover' );
	}

	/* ------------------------------------------------------------------
	 * Notices
	 * ------------------------------------------------------------------ */

	/**
	 * Elementor is not active.
	 */
	public function admin_notice_missing_elementor() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'Webcasata Elementor Addons requires Elementor to be installed and activated.', 'webcasata-elementor-addons' ) .
			'</p></div>';
	}

	/**
	 * Elementor is too old.
	 */
	public function admin_notice_old_elementor() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' .
			esc_html(
				sprintf(
					/* translators: %s: minimum Elementor version. */
					__( 'Webcasata Elementor Addons requires Elementor %s or newer. Please update Elementor.', 'webcasata-elementor-addons' ),
					WEBCASATA_ADDONS_MIN_ELEMENTOR
				)
			) .
			'</p></div>';
	}

	/* ------------------------------------------------------------------
	 * Elementor registration
	 * ------------------------------------------------------------------ */

	/**
	 * The "Webcasata" widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor's category manager.
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'webcasata',
			[
				'title' => __( 'Webcasata', 'webcasata-elementor-addons' ),
				'icon'  => 'eicon-apps',
			]
		);
	}

	/**
	 * Register only the widgets that are switched on (and whose requirements,
	 * like the separate Content Filter plugin, are met).
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor's widget manager.
	 */
	public function register_widgets( $widgets_manager ) {
		foreach ( $this->settings->active_widgets() as $def ) {
			require_once QYRUS_ACCORDION_PATH . $def['file'];

			$class = '\\' . $def['class'];
			$widgets_manager->register( new $class() );
		}
	}

	/**
	 * Register the JS handles of the active widgets. Elementor enqueues a
	 * handle only on pages where the widget is used (get_script_depends).
	 */
	public function register_scripts() {
		foreach ( $this->settings->active_widgets() as $def ) {
			foreach ( $def['scripts'] as $handle => $path ) {
				wp_register_script(
					$handle,
					QYRUS_ACCORDION_URL . $path,
					[ 'jquery', 'elementor-frontend' ],
					QYRUS_ACCORDION_VERSION,
					true
				);
			}
		}
	}

	/**
	 * Register the CSS handles of the active widgets (see register_scripts()).
	 */
	public function register_styles() {
		foreach ( $this->settings->active_widgets() as $def ) {
			foreach ( $def['styles'] as $handle => $path ) {
				wp_register_style(
					$handle,
					QYRUS_ACCORDION_URL . $path,
					[],
					QYRUS_ACCORDION_VERSION
				);
			}
		}
	}
}
