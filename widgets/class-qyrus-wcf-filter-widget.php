<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Thin wrapper around the "Webcasata Content Filter" plugin.
 *
 * It does not duplicate any rendering logic: it looks up the same
 * `wbcf_filter` posts the plugin's own admin screen, shortcode
 * ([wbcf_filter id="X"]) and Gutenberg block use, and calls the exact
 * same WBCF_Render::render_filter() method those use. So the output is
 * guaranteed to always match the shortcode/block for a given filter,
 * and any future WBCF template/feature automatically works here too.
 *
 * NOTE: the Content Filter plugin's internal prefix is "WBCF_" (not
 * "WCF_" -- that was changed on their end to avoid colliding with
 * WooCommerce's "WC_" namespace; see WBCF_POST_TYPE's own comment in
 * their main plugin file). This widget's own PHP class name below
 * keeps the old "WCF" spelling for backwards compatibility with sites
 * that already have this widget placed on pages -- renaming it would
 * break every existing Elementor template that references it by name.
 *
 * This widget only ever gets registered (see the main plugin file) if
 * WBCF_Render already exists, so the class_exists() checks below are a
 * defensive second layer, not the primary guard.
 */
class Qyrus_WCF_Filter_Widget extends Widget_Base {

	public function get_name() {
		return 'qyrus-wcf-filter';
	}

	public function get_title() {
		return __( 'Webcasata Content Filter', 'webcasata-elementor-addons' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return [ 'webcasata' ];
	}

	public function get_keywords() {
		return [ 'content filter', 'wcf', 'filter', 'posts', 'ajax', 'webcasata' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_filter', [
			'label' => __( 'Content Filter', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		if ( ! class_exists( 'WBCF_Render' ) ) {
			$this->add_control( 'wcf_missing_notice', [
				'raw'             => __( 'The "Webcasata Content Filter" plugin isn\'t active. Install and activate it, then reload this page to select a filter here.', 'webcasata-elementor-addons' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
			] );
			$this->end_controls_section();
			return;
		}

		$this->add_control( 'filter_id', [
			'label'       => __( 'Select Filter', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => $this->get_filter_options(),
			'default'     => '0',
			'description' => __( 'Filters are created and configured under Content Filters in the WordPress admin menu.', 'webcasata-elementor-addons' ),
		] );

		$this->add_control( 'overrides_heading', [
			'label'     => __( 'Overrides (Optional)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'override_posts_per_page', [
			'label'       => __( 'Posts Per Page', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'default'     => 0,
			'description' => __( 'Leave at 0 to use the number set on the filter itself.', 'webcasata-elementor-addons' ),
		] );

		$this->add_control( 'override_template', [
			'label'       => __( 'Template', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => $this->get_template_options(),
			'default'     => '',
			'description' => __( "Leave on \"Don't Override\" to use the template set on the filter itself.", 'webcasata-elementor-addons' ),
		] );

		$this->end_controls_section();

		$this->register_display_section();
	}

	/**
	 * The Content Filter plugin has no per-placement "hide this part of
	 * the card" option -- title/meta/author/comments/read-more/image/
	 * excerpt are baked into its template files and shared by every
	 * shortcode/block/widget placement of a given filter. So these
	 * toggles work by injecting CSS scoped to just this widget instance
	 * (render_hide_styles()), rather than by passing anything through
	 * to WBCF_Render.
	 *
	 * The default selectors target the BEM class names used by this
	 * plugin's built-in templates (Classic, Cards, Magazine, Minimal,
	 * News) -- e.g. "wcf-card__title" / "wbcf-card__title" -- matched
	 * with [class*="..."] so they survive either class-name prefix. In
	 * the Classic template, "Meta" (date + category) and the
	 * author/comments/read-more line live in two separate markup
	 * blocks (card__meta vs. card__footer), which is why they're
	 * separate toggles here rather than one combined "Meta" toggle. If
	 * the filter uses a different template (including a Pro one) with
	 * different class names, edit the matching "Selector (Advanced)"
	 * field once the toggle reveals it.
	 */
	private function register_display_section() {
		$this->start_controls_section( 'section_display', [
			'label' => __( 'Display', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'display_notice', [
			'raw'             => __( 'These hide parts of each card via CSS scoped to this widget only. Defaults match the class names used by this plugin\'s built-in templates -- if a card part doesn\'t hide, edit its selector once the toggle is on. To match more than one class in a single field, separate them with commas, e.g. [class*="card__author"], [class*="card__comments"] (each one is treated as its own target -- space-separating them instead means "nested inside," which usually won\'t match).', 'webcasata-elementor-addons' ),
			'type'            => Controls_Manager::RAW_HTML,
			'content_classes' => 'elementor-descriptor',
		] );

		$this->add_control( 'hide_title', [
			'label'   => __( 'Hide Title', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'title_hide_selector', [
			'label'     => __( 'Title Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '[class*="card__title"]',
			'condition' => [ 'hide_title' => 'yes' ],
		] );

		$this->add_control( 'hide_meta', [
			'label'     => __( 'Hide Meta Tags', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => '',
			'separator' => 'before',
		] );

		$this->add_control( 'meta_hide_selector', [
			'label'       => __( 'Meta Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => '[class*="card__meta"]',
			'description' => __( 'Covers date and category -- the template\'s own "meta" block. Author, comments and Read More live in a separate footer block, so they have their own toggles below.', 'webcasata-elementor-addons' ),
			'condition'   => [ 'hide_meta' => 'yes' ],
		] );

		$this->add_control( 'hide_author', [
			'label'     => __( 'Hide Author', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => '',
			'separator' => 'before',
		] );

		$this->add_control( 'author_hide_selector', [
			'label'     => __( 'Author Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '[class*="card__author"]',
			'condition' => [ 'hide_author' => 'yes' ],
		] );

		$this->add_control( 'hide_comments', [
			'label'     => __( 'Hide Comments', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => '',
			'separator' => 'before',
		] );

		$this->add_control( 'comments_hide_selector', [
			'label'     => __( 'Comments Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '[class*="card__comments"]',
			'condition' => [ 'hide_comments' => 'yes' ],
		] );

		$this->add_control( 'hide_readmore', [
			'label'     => __( 'Hide Read More', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => '',
			'separator' => 'before',
		] );

		$this->add_control( 'readmore_hide_selector', [
			'label'     => __( 'Read More Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '[class*="card__readmore"]',
			'condition' => [ 'hide_readmore' => 'yes' ],
		] );

		$this->add_control( 'hide_image', [
			'label'     => __( 'Hide Featured Image', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => '',
			'separator' => 'before',
		] );

		$this->add_control( 'image_hide_selector', [
			'label'     => __( 'Image Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '[class*="card__thumb"], [class*="card__image"]',
			'condition' => [ 'hide_image' => 'yes' ],
		] );

		$this->add_control( 'hide_excerpt', [
			'label'     => __( 'Hide Excerpt', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SWITCHER,
			'default'   => '',
			'separator' => 'before',
		] );

		$this->add_control( 'excerpt_hide_selector', [
			'label'     => __( 'Excerpt Selector (Advanced)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => '[class*="card__excerpt"]',
			'condition' => [ 'hide_excerpt' => 'yes' ],
		] );

		$this->end_controls_section();
	}

	/**
	 * Mirrors WBCF_Block::enqueue_editor_assets()'s filter query exactly,
	 * so this dropdown always lists the same filters, in the same
	 * order, as the Gutenberg block's picker.
	 */
	private function get_filter_options() {
		$options = [
			'0' => __( '— Select a filter —', 'webcasata-elementor-addons' ),
		];

		$post_type = defined( 'WBCF_POST_TYPE' ) ? WBCF_POST_TYPE : 'wbcf_filter';

		$filters = get_posts( [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		if ( empty( $filters ) ) {
			$options['0'] = __( '— No filters found. Create one under Content Filters. —', 'webcasata-elementor-addons' );
			return $options;
		}

		foreach ( $filters as $filter ) {
			$options[ (string) $filter->ID ] = $filter->post_title;
		}

		return $options;
	}

	/**
	 * Mirrors WBCF_Block::build_template_options(): "Don't Override" plus
	 * every registered template, including any added by the Pro plugin
	 * via the wbcf_template_choices filter.
	 */
	private function get_template_options() {
		$options = [
			'' => __( "Don't Override", 'webcasata-elementor-addons' ),
		];

		if ( method_exists( 'WBCF_Render', 'get_template_choices' ) ) {
			foreach ( WBCF_Render::get_template_choices() as $slug => $label ) {
				$options[ $slug ] = $label;
			}
		}

		return $options;
	}

	private function is_editor_preview() {
		return \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	private function render_placeholder( $message ) {
		if ( ! $this->is_editor_preview() ) {
			return;
		}
		printf(
			'<p style="margin:0;padding:16px;border:1px dashed #c3c4c7;border-radius:4px;color:#646970;font-size:13px;">%s</p>',
			esc_html( $message )
		);
	}

	protected function render() {
		if ( ! class_exists( 'WBCF_Render' ) ) {
			$this->render_placeholder( __( 'The "Webcasata Content Filter" plugin is not active.', 'webcasata-elementor-addons' ) );
			return;
		}

		$settings  = $this->get_settings_for_display();
		$filter_id = absint( $settings['filter_id'] );

		if ( ! $filter_id ) {
			$this->render_placeholder( __( 'Select a Webcasata Content Filter in the widget settings.', 'webcasata-elementor-addons' ) );
			return;
		}

		if ( class_exists( 'WBCF_Assets' ) && method_exists( 'WBCF_Assets', 'instance' ) ) {
			// Same call the shortcode makes -- ensures CSS/JS load even
			// though this widget's markup never sits in $post->post_content.
			WBCF_Assets::instance()->mark_active();
		}

		$overrides = [];
		if ( ! empty( $settings['override_posts_per_page'] ) ) {
			$overrides['posts_per_page'] = absint( $settings['override_posts_per_page'] ); // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Editor-chosen value for the Content Filter plugin.
		}
		if ( ! empty( $settings['override_template'] ) ) {
			$overrides['template'] = sanitize_key( $settings['override_template'] );
		}

		$this->render_hide_styles( $settings );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WBCF_Render::render_filter() builds fully escaped markup itself, identically to the shortcode/block.
		echo WBCF_Render::render_filter( $filter_id, $overrides );
	}

	/**
	 * Prints a <style> block, scoped to this widget instance only via
	 * Elementor's own ".elementor-element-{id}" wrapper class (the same
	 * class every widget's outer container already carries -- no extra
	 * wrapper markup needed here), that hides whichever card parts were
	 * toggled off. No-ops if every toggle is off.
	 */
	private function render_hide_styles( $settings ) {
		$toggles = [
			'hide_title'    => 'title_hide_selector',
			'hide_meta'     => 'meta_hide_selector',
			'hide_author'   => 'author_hide_selector',
			'hide_comments' => 'comments_hide_selector',
			'hide_readmore' => 'readmore_hide_selector',
			'hide_image'    => 'image_hide_selector',
			'hide_excerpt'  => 'excerpt_hide_selector',
		];

		$scope = '.elementor-element-' . $this->get_id();
		$rules = [];

		foreach ( $toggles as $toggle_key => $selector_key ) {
			if ( 'yes' !== $settings[ $toggle_key ] || empty( $settings[ $selector_key ] ) ) {
				continue;
			}
			foreach ( explode( ',', $settings[ $selector_key ] ) as $part ) {
				// wp_strip_all_tags guards against a selector value that
				// tries to break out of the <style> tag; ordinary CSS
				// selector syntax (brackets, quotes, *, ., #, :, etc.)
				// passes through untouched.
				// Braces, semicolons and angle brackets are removed as well, so a
				// value can never close the rule or start a new one.
				$part = trim( preg_replace( '/[{};<>]/', '', wp_strip_all_tags( $part ) ) );
				if ( '' !== $part ) {
					$rules[] = $scope . ' ' . $part;
				}
			}
		}

		if ( empty( $rules ) ) {
			return;
		}

		// Every rule was stripped of tags / braces / semicolons above.
		printf( '<style>%s{display:none !important;}</style>', implode( ',', $rules ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
