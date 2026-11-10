<?php
/**
 * Settings: the registry of everything the plugin offers (widgets, extensions)
 * and the admin page that switches each one on or off.
 *
 * ADDING A NEW WIDGET LATER
 * -------------------------
 * 1. Drop the widget class file in /widgets and its CSS / JS in /assets.
 * 2. Add one entry to Webcasata_Settings::widgets() below (slug, file, class,
 *    styles, scripts) and its title / description to Webcasata_Settings::labels().
 *
 * That is all: the settings page shows a toggle for it, the loader registers the
 * widget (and its CSS / JS handles) only while the toggle is on, and new
 * widgets are ON by default for people who already saved their settings.
 *
 * @package Webcasata_Elementor_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registry + settings page.
 */
final class Webcasata_Settings {

	const OPTION   = 'webcasata_addons_settings';
	const GROUP    = 'webcasata_addons';
	const PAGE     = 'webcasata-addons';
	const CAPABILITY = 'manage_options';

	/**
	 * Single instance.
	 *
	 * @var Webcasata_Settings|null
	 */
	private static $instance = null;

	/**
	 * Cached, normalised option value.
	 *
	 * @var array|null
	 */
	private $values = null;

	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Get the single instance.
	 *
	 * @return Webcasata_Settings
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook the admin side. Runs on every request, but only adds admin hooks.
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_setting' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( QYRUS_ACCORDION_FILE ), [ $this, 'action_links' ] );
		add_action( 'update_option_' . self::OPTION, [ $this, 'flush_cache' ] );
		add_action( 'add_option_' . self::OPTION, [ $this, 'flush_cache' ] );
	}

	/* ------------------------------------------------------------------
	 * Registry
	 * ------------------------------------------------------------------ */

	/**
	 * Every widget the plugin can register.
	 *
	 * Keys: file (relative to the plugin), class, styles
	 * and scripts (handle => path relative to the plugin), requires_class (optional: a class that must exist,
	 * e.g. another plugin). Titles and descriptions live in labels().
	 *
	 * @return array
	 */
	public static function widgets() {
		return [
			'accordion'      => [
				'file'        => 'widgets/class-qyrus-home-accordion-widget.php',
				'class'       => 'Qyrus_Home_Accordion_Widget',
				'styles'      => [ 'qyrus-accordion' => 'assets/css/qyrus-accordion.css' ],
				'scripts'     => [ 'qyrus-accordion' => 'assets/js/qyrus-accordion.js' ],
			],
			'feature-grid'   => [
				'file'        => 'widgets/class-qyrus-feature-grid-widget.php',
				'class'       => 'Qyrus_Feature_Grid_Widget',
				'styles'      => [ 'qyrus-feature-grid' => 'assets/css/qyrus-feature-grid.css' ],
				'scripts'     => [],
			],
			'icon-scroll'    => [
				'file'        => 'widgets/class-qyrus-icon-marquee-widget.php',
				'class'       => 'Qyrus_Icon_Marquee_Widget',
				'styles'      => [ 'qyrus-icon-marquee' => 'assets/css/qyrus-icon-marquee.css' ],
				'scripts'     => [],
			],
			'icon-box'       => [
				'file'        => 'widgets/class-webcasata-icon-box-widget.php',
				'class'       => 'Webcasata_Icon_Box_Widget',
				'styles'      => [ 'webcasata-icon-box' => 'assets/css/webcasata-icon-box.css' ],
				'scripts'     => [],
			],
			'video'          => [
				'file'        => 'widgets/class-webcasata-video-widget.php',
				'class'       => 'Webcasata_Video_Widget',
				'styles'      => [ 'webcasata-video' => 'assets/css/webcasata-video.css' ],
				'scripts'     => [ 'webcasata-video' => 'assets/js/webcasata-video.js' ],
			],
			'content-filter' => [
				'file'           => 'widgets/class-qyrus-wcf-filter-widget.php',
				'class'          => 'Qyrus_WCF_Filter_Widget',
				'styles'         => [],
				'scripts'        => [],
				'requires_class' => 'WBCF_Render',
			],
		];
	}

	/**
	 * Editor extensions (they add controls to Elementor's own elements).
	 *
	 * @return array
	 */
	public static function extensions() {
		return [
			'pseudo-elements' => [],
			'parent-hover'    => [],
		];
	}

	/**
	 * Translated labels for the settings screen. Kept apart from widgets() /
	 * extensions() because those run early (plugins_loaded), and translating
	 * before the "init" action triggers a WordPress 6.7+ notice. Only call
	 * this from admin screens.
	 *
	 * @param string $type "widgets" or "extensions".
	 * @return array slug => [ title, description, requires_label? ]
	 */
	public static function labels( $type ) {
		if ( 'extensions' === $type ) {
			return [
				'pseudo-elements' => [
					'title' => __( 'Container :before / :after', 'webcasata-elementor-addons' ),
					'description' => __( 'Adds :before / :after pseudo-element controls (Normal + Hover) to Elementor Containers. The Icon Box widget keeps its own copy of these controls even when this is off.', 'webcasata-elementor-addons' ),
				],
				'parent-hover' => [
					'title' => __( 'Style On Parent Hover', 'webcasata-elementor-addons' ),
					'description' => __( 'Adds a "Style On Parent Hover" section to the Advanced tab of every widget and container.', 'webcasata-elementor-addons' ),
				],
			];
		}
		return [
			'accordion' => [
				'title' => __( 'Accordion', 'webcasata-elementor-addons' ),
				'description' => __( 'Left / right accordion where the image swaps per item, with its own mobile inline-image layout.', 'webcasata-elementor-addons' ),
			],
			'feature-grid' => [
				'title' => __( 'Feature Grid', 'webcasata-elementor-addons' ),
				'description' => __( 'Icon + title + description cards in a responsive grid.', 'webcasata-elementor-addons' ),
			],
			'icon-scroll' => [
				'title' => __( 'Icon Scroll', 'webcasata-elementor-addons' ),
				'description' => __( 'Infinite vertical / horizontal auto-scrolling icon wall (CSS only, no JavaScript).', 'webcasata-elementor-addons' ),
			],
			'icon-box' => [
				'title' => __( 'Icon Box', 'webcasata-elementor-addons' ),
				'description' => __( 'Icon with a styleable background, focused title, text fill / stroke / shadow, title separator and :before / :after pseudo-elements.', 'webcasata-elementor-addons' ),
			],
			'video' => [
				'title' => __( 'Video', 'webcasata-elementor-addons' ),
				'description' => __( 'YouTube / Vimeo / self-hosted video that is only loaded when played: in a popup or inline. Supports autoplay when scrolled into view.', 'webcasata-elementor-addons' ),
			],
			'content-filter' => [
				'title' => __( 'Content Filter', 'webcasata-elementor-addons' ),
				'description' => __( 'Drops a saved Webcasata Content Filter filter onto the page.', 'webcasata-elementor-addons' ),
				'requires_label' => __( 'Needs the separate "Webcasata Content Filter" plugin to be active.', 'webcasata-elementor-addons' ),
			],
		];
	}

	/* ------------------------------------------------------------------
	 * Reading the settings
	 * ------------------------------------------------------------------ */

	/**
	 * Normalised settings: anything missing (first install, or a widget added in
	 * a later version) counts as enabled.
	 *
	 * @return array { master: bool, widgets: array<string,bool>, extensions: array<string,bool> }
	 */
	public function get() {
		if ( null !== $this->values ) {
			return $this->values;
		}

		$saved = get_option( self::OPTION, [] );
		$saved = is_array( $saved ) ? $saved : [];

		$values = [
			'master'     => ! isset( $saved['master'] ) || 'yes' === $saved['master'],
			'widgets'    => [],
			'extensions' => [],
		];

		foreach ( array_keys( self::widgets() ) as $slug ) {
			$values['widgets'][ $slug ] = ! isset( $saved['widgets'][ $slug ] ) || 'yes' === $saved['widgets'][ $slug ];
		}
		foreach ( array_keys( self::extensions() ) as $slug ) {
			$values['extensions'][ $slug ] = ! isset( $saved['extensions'][ $slug ] ) || 'yes' === $saved['extensions'][ $slug ];
		}

		$this->values = $values;
		return $values;
	}

	/**
	 * Drop the cache after the option changes.
	 */
	public function flush_cache() {
		$this->values = null;
	}

	/**
	 * Is the whole plugin switched on?
	 *
	 * @return bool
	 */
	public function is_master_enabled() {
		$values = $this->get();
		return $values['master'];
	}

	/**
	 * Is one widget switched on (master switch included)?
	 *
	 * @param string $slug Widget slug from self::widgets().
	 * @return bool
	 */
	public function is_widget_enabled( $slug ) {
		$values = $this->get();
		return $values['master'] && ! empty( $values['widgets'][ $slug ] );
	}

	/**
	 * Is one extension switched on (master switch included)?
	 *
	 * @param string $slug Extension slug from self::extensions().
	 * @return bool
	 */
	public function is_extension_enabled( $slug ) {
		$values = $this->get();
		return $values['master'] && ! empty( $values['extensions'][ $slug ] );
	}

	/**
	 * Widgets that are switched on and whose requirements are met.
	 *
	 * @return array slug => definition
	 */
	public function active_widgets() {
		$active = [];
		foreach ( self::widgets() as $slug => $def ) {
			if ( ! $this->is_widget_enabled( $slug ) ) {
				continue;
			}
			if ( ! empty( $def['requires_class'] ) && ! class_exists( $def['requires_class'] ) ) {
				continue;
			}
			$active[ $slug ] = $def;
		}
		return $active;
	}

	/* ------------------------------------------------------------------
	 * Saving
	 * ------------------------------------------------------------------ */

	/**
	 * Register the option. WordPress checks the nonce and the capability when the
	 * form posts to options.php.
	 */
	public function register_setting() {
		register_setting(
			self::GROUP,
			self::OPTION,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize' ],
				'default'           => [],
				'show_in_rest'      => false,
			]
		);
	}

	/**
	 * Whitelist: only known keys, only "yes" / "no".
	 *
	 * @param mixed $input Raw posted value.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input = is_array( $input ) ? $input : [];

		$clean = [
			'master'     => ! empty( $input['master'] ) ? 'yes' : 'no',
			'widgets'    => [],
			'extensions' => [],
		];

		foreach ( array_keys( self::widgets() ) as $slug ) {
			$clean['widgets'][ $slug ] = ! empty( $input['widgets'][ $slug ] ) ? 'yes' : 'no';
		}
		foreach ( array_keys( self::extensions() ) as $slug ) {
			$clean['extensions'][ $slug ] = ! empty( $input['extensions'][ $slug ] ) ? 'yes' : 'no';
		}

		return $clean;
	}

	/* ------------------------------------------------------------------
	 * Admin page
	 * ------------------------------------------------------------------ */

	/**
	 * Settings > Webcasata Addons.
	 */
	public function add_menu() {
		$this->page_hook = (string) add_options_page(
			__( 'Webcasata Addons', 'webcasata-elementor-addons' ),
			__( 'Webcasata Addons', 'webcasata-elementor-addons' ),
			self::CAPABILITY,
			self::PAGE,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * "Settings" link on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function action_links( $links ) {
		$url  = admin_url( 'options-general.php?page=' . self::PAGE );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'webcasata-elementor-addons' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Admin CSS / JS: this one screen only.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( '' === $this->page_hook || $hook_suffix !== $this->page_hook ) {
			return;
		}

		wp_enqueue_style(
			'webcasata-addons-admin',
			QYRUS_ACCORDION_URL . 'assets/admin/webcasata-admin.css',
			[],
			QYRUS_ACCORDION_VERSION
		);
		wp_enqueue_script(
			'webcasata-addons-admin',
			QYRUS_ACCORDION_URL . 'assets/admin/webcasata-admin.js',
			[],
			QYRUS_ACCORDION_VERSION,
			true
		);
	}

	/**
	 * One toggle row.
	 *
	 * @param string $name        Full input name.
	 * @param bool   $checked     Current state.
	 * @param string $title       Label.
	 * @param string $description Help text.
	 * @param string $note        Optional warning (e.g. missing dependency).
	 */
	private function render_toggle( $name, $checked, $title, $description, $note = '' ) {
		$id = 'wa-' . sanitize_html_class( str_replace( [ '[', ']' ], '-', $name ) );
		?>
		<div class="wa-card<?php echo $checked ? ' is-on' : ''; ?>">
			<label class="wa-switch" for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> />
				<span class="wa-switch-track" aria-hidden="true"></span>
				<span class="wa-card-text">
					<span class="wa-card-title"><?php echo esc_html( $title ); ?></span>
					<span class="wa-card-desc"><?php echo esc_html( $description ); ?></span>
					<?php if ( '' !== $note ) : ?>
						<span class="wa-card-note"><?php echo esc_html( $note ); ?></span>
					<?php endif; ?>
				</span>
			</label>
		</div>
		<?php
	}

	/**
	 * Output the settings screen.
	 */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$values = $this->get();
		$opt    = self::OPTION;
		?>
		<div class="wrap wa-wrap">
			<h1><?php esc_html_e( 'Webcasata Addons', 'webcasata-elementor-addons' ); ?></h1>
			<p class="wa-intro">
				<?php esc_html_e( 'Switch widgets and extensions on or off. Anything that is off is not registered with Elementor and loads no CSS or JavaScript. Widgets that are on only load their files on pages where the widget is actually used.', 'webcasata-elementor-addons' ); ?>
			</p>

			<?php settings_errors(); ?>

			<form method="post" action="options.php" class="wa-form">
				<?php settings_fields( self::GROUP ); ?>

				<div class="wa-master">
					<?php
					$this->render_toggle(
						$opt . '[master]',
						$values['master'],
						__( 'Enable Webcasata Addons', 'webcasata-elementor-addons' ),
						__( 'Master switch. When off, no widget or extension loads and the "Webcasata" category disappears from the Elementor panel. Your choices below are kept.', 'webcasata-elementor-addons' )
					);
					?>
				</div>

				<div class="wa-sections<?php echo $values['master'] ? '' : ' is-master-off'; ?>">

					<div class="wa-section-head">
						<h2><?php esc_html_e( 'Widgets', 'webcasata-elementor-addons' ); ?></h2>
						<span class="wa-bulk">
							<button type="button" class="button-link" data-wa-bulk="on"><?php esc_html_e( 'Enable all', 'webcasata-elementor-addons' ); ?></button>
							<span aria-hidden="true">|</span>
							<button type="button" class="button-link" data-wa-bulk="off"><?php esc_html_e( 'Disable all', 'webcasata-elementor-addons' ); ?></button>
						</span>
					</div>
					<div class="wa-grid">
						<?php
						$labels = self::labels( 'widgets' );
						foreach ( self::widgets() as $slug => $def ) {
							$label   = $labels[ $slug ];
							$missing = ! empty( $def['requires_class'] ) && ! class_exists( $def['requires_class'] );
							$this->render_toggle(
								$opt . '[widgets][' . $slug . ']',
								$values['widgets'][ $slug ],
								$label['title'],
								$label['description'],
								$missing && ! empty( $label['requires_label'] ) ? $label['requires_label'] : ''
							);
						}
						?>
					</div>

					<div class="wa-section-head">
						<h2><?php esc_html_e( 'Extensions', 'webcasata-elementor-addons' ); ?></h2>
					</div>
					<div class="wa-grid">
						<?php
						$labels = self::labels( 'extensions' );
						foreach ( self::extensions() as $slug => $def ) {
							$label = $labels[ $slug ];
							$this->render_toggle(
								$opt . '[extensions][' . $slug . ']',
								$values['extensions'][ $slug ],
								$label['title'],
								$label['description']
							);
						}
						?>
					</div>

					<p class="description wa-foot">
						<?php esc_html_e( 'Turning a widget off hides it from the Elementor panel. Pages that already use it show nothing in its place until you turn it back on; no content is deleted.', 'webcasata-elementor-addons' ); ?>
					</p>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
