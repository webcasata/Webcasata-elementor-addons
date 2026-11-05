<?php
/**
 * Webcasata Pseudo Elements — Container extension (Phase 1).
 *
 * Adds :before / :after controls to Elementor's own Container, so common
 * decoration (overlays, underlines, gradient borders, ribbons, shapes) no
 * longer needs hand-written custom CSS.
 *
 *  - Layout tab : two switches, ":before" and ":after".
 *  - Style tab  : one "Pseudo :before" / "Pseudo :after" section per enabled
 *                 pseudo-element, each with Normal and Hover tabs.
 *  - Hover tab  : a "Hover Trigger" — this container, its direct parent
 *                 container, or any element with a custom class.
 *
 * Everything is written through Elementor's normal `selectors` system, so the
 * CSS is generated per element, is responsive, and updates live in the editor.
 *
 * Elementor facts this relies on (checked against Elementor's source):
 *  - Only `{{ID}}` and `{{WRAPPER}}` are replaced inside selector keys, so the
 *    hover trigger is chosen with a class added by `prefix_class`, and each
 *    hover control lists one selector per trigger.
 *  - CSS is only generated for controls whose OWN `condition` passes (a section
 *    condition is not enough), so every control below carries the condition.
 *  - An empty control value produces no CSS at all.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;

final class Webcasata_Pseudo_Elements {

	const PREFIX = 'wpe_';

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Layout tab: the two on/off switches.
		add_action(
			'elementor/element/container/section_layout_additional_options/after_section_end',
			[ $this, 'add_layout_controls' ],
			10,
			2
		);

		// Style tab: the style sections (after Shape Divider, last of the Style sections).
		add_action(
			'elementor/element/container/section_shape_divider/after_section_end',
			[ $this, 'add_style_controls' ],
			10,
			2
		);

		// Keeps a typed "content" string from breaking out of its CSS quotes.
		add_filter( 'elementor/files/css/property', [ $this, 'escape_content_value' ], 10, 4 );

		// Load the icon font that a pseudo-element's Content code needs.
		add_action( 'elementor/frontend/container/before_render', [ $this, 'maybe_enqueue_icon_font' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_all_icon_fonts' ] );

		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/* ------------------------------------------------------------------
	 * Assets
	 * ------------------------------------------------------------------ */

	public function register_scripts() {
		wp_register_script(
			'webcasata-pseudo-hover',
			QYRUS_ACCORDION_URL . 'assets/js/webcasata-pseudo-hover.js',
			[ 'jquery', 'elementor-frontend' ],
			QYRUS_ACCORDION_VERSION,
			true
		);
	}

	public function enqueue_scripts() {
		// Tiny file; it only does work for containers using the "Custom class" trigger.
		wp_enqueue_script( 'webcasata-pseudo-hover' );
	}

	/** Icon Font option => Elementor's registered style handle. */
	private function icon_font_handles() {
		return [
			'fa-solid'   => 'elementor-icons-fa-solid',
			'fa-regular' => 'elementor-icons-fa-regular',
			'fa-brands'  => 'elementor-icons-fa-brands',
			'eicons'     => 'elementor-icons',
		];
	}

	private function enqueue_handle( $handle ) {
		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}

	/** Front end: only the fonts this container actually uses. */
	public function maybe_enqueue_icon_font( $element ) {
		$handles = $this->icon_font_handles();
		foreach ( $this->pseudos() as $pseudo ) {
			if ( 'yes' !== $element->get_settings( $this->id( $pseudo, 'enable' ) ) ) {
				continue;
			}
			$font = $element->get_settings( $this->id( $pseudo, 'font' ) );
			if ( $font && isset( $handles[ $font ] ) ) {
				$this->enqueue_handle( $handles[ $font ] );
			}
		}
	}

	/** Editor preview renders containers with JS templates, so PHP can't tell: load them all there. */
	public function enqueue_all_icon_fonts() {
		foreach ( $this->icon_font_handles() as $handle ) {
			$this->enqueue_handle( $handle );
		}
	}

	/* ------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------ */

	private function pseudos() {
		return [ 'before', 'after' ];
	}

	/** Control id for a pseudo: ('before', 'width') => 'wpe_before_width'. */
	private function id( $pseudo, $name ) {
		return self::PREFIX . $pseudo . '_' . $name;
	}

	/** Selector for the pseudo-element itself: {{WRAPPER}}::before */
	private function sel( $pseudo ) {
		return '{{WRAPPER}}::' . $pseudo;
	}

	/**
	 * Hover selector list, one entry per trigger. Each is gated by the class
	 * that the Hover Trigger control puts on the container, so only the chosen
	 * trigger is live. The gate also raises specificity above the Normal rule.
	 *
	 *  self   : the container itself is hovered
	 *  parent : the direct parent container is hovered (boxed containers have
	 *           an .e-con-inner between parent and child, so both are listed)
	 *  custom : JS adds .wpe-{pseudo}-hover while the chosen element is hovered
	 */
	private function hover_sel( $pseudo ) {
		$pe  = '::' . $pseudo;
		$gate = '.wpe-' . $pseudo . '-trg-';

		return implode(
			', ',
			[
				'{{WRAPPER}}' . $gate . 'self:hover' . $pe,
				'.elementor-element:hover > .elementor-element-{{ID}}' . $gate . 'parent' . $pe,
				'.elementor-element:hover > .e-con-inner > .elementor-element-{{ID}}' . $gate . 'parent' . $pe,
				'{{WRAPPER}}' . $gate . 'custom.wpe-' . $pseudo . '-hover' . $pe,
			]
		);
	}

	/**
	 * Add a normal control, always conditioned on the pseudo being enabled.
	 * `selectors` keys may use the literal token {{SEL}}, which becomes the
	 * Normal or Hover selector list for this pseudo.
	 */
	private function control( $element, $pseudo, $mode, $name, array $args, $responsive = false ) {
		$args = $this->prepare_args( $pseudo, $mode, $args );
		$id   = $this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . $name );

		if ( $responsive ) {
			$element->add_responsive_control( $id, $args );
		} else {
			$element->add_control( $id, $args );
		}
	}

	private function group( $element, $pseudo, $mode, $type, $name, array $args ) {
		$args['name']     = $this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . $name );
		$args['selector'] = 'hover' === $mode ? $this->hover_sel( $pseudo ) : $this->sel( $pseudo );
		$args['condition'] = array_merge(
			[ $this->id( $pseudo, 'enable' ) => 'yes' ],
			isset( $args['condition'] ) ? $args['condition'] : []
		);
		$element->add_group_control( $type::get_type(), $args );
	}

	private function prepare_args( $pseudo, $mode, array $args ) {
		$args['condition'] = array_merge(
			[ $this->id( $pseudo, 'enable' ) => 'yes' ],
			isset( $args['condition'] ) ? $args['condition'] : []
		);

		if ( ! empty( $args['selectors'] ) ) {
			$list     = 'hover' === $mode ? $this->hover_sel( $pseudo ) : $this->sel( $pseudo );
			$resolved = [];
			foreach ( $args['selectors'] as $key => $css ) {
				// {{SEL}} = Normal or Hover list for the current tab; {{HOVER}} = always the Hover list.
				$key = str_replace( [ '{{SEL}}', '{{HOVER}}' ], [ $list, $this->hover_sel( $pseudo ) ], $key );
				$resolved[ $key ] = $css;
			}
			$args['selectors'] = $resolved;
		}

		return $args;
	}

	private function length_units() {
		return [ 'px', '%', 'em', 'rem', 'vw', 'vh', 'custom' ];
	}

	private function length_range() {
		return [
			'px'  => [ 'min' => -1000, 'max' => 1000 ],
			'%'   => [ 'min' => -200, 'max' => 200 ],
			'em'  => [ 'min' => -50, 'max' => 50, 'step' => 0.1 ],
			'rem' => [ 'min' => -50, 'max' => 50, 'step' => 0.1 ],
			'vw'  => [ 'min' => -100, 'max' => 100 ],
			'vh'  => [ 'min' => -100, 'max' => 100 ],
		];
	}

	/* ------------------------------------------------------------------
	 * Layout tab
	 * ------------------------------------------------------------------ */

	public function add_layout_controls( $element, $args = [] ) {
		$element->start_controls_section(
			'wpe_section_layout',
			[
				'label' => esc_html__( 'Pseudo Elements', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control(
			'wpe_intro',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Switch on :before and/or :after to get a styling block for each in the Style tab (no custom CSS needed).', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		foreach ( $this->pseudos() as $pseudo ) {
			$element->add_control(
				$this->id( $pseudo, 'enable' ),
				[
					'label'        => ':' . $pseudo,
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'On', 'webcasata-elementor-addons' ),
					'label_off'    => esc_html__( 'Off', 'webcasata-elementor-addons' ),
					'return_value' => 'yes',
					'default'      => '',
					// Read by assets/js/webcasata-pseudo-hover.js (Custom class trigger).
					'frontend_available' => true,
					// Base rule: without content the pseudo-element does not exist at all.
					// Elementor's own CSS already styles ::before on every container (it is
					// the Background Overlay layer: full-size, and it copies the container's
					// border), so ::before also gets its inherited border switched off.
					'selectors'    => [
						$this->sel( $pseudo ) => 'before' === $pseudo
							? 'content: ""; display: block; border: 0 none;'
							: 'content: ""; display: block;',
					],
				]
			);
		}

		$element->end_controls_section();
	}


	/* ------------------------------------------------------------------
	 * Presets (Phase 3)
	 * ------------------------------------------------------------------ */

	/**
	 * Each preset is a starting layer of CSS (Normal, Hover, and the container
	 * itself). It is registered BEFORE the Normal/Hover controls, so anything the
	 * user sets there lands later in the same rule and wins. The preset's own
	 * knobs are CSS variables on the pseudo-element (--wpe-c1, --wpe-c2,
	 * --wpe-size, --wpe-fx/--wpe-fy), each with a fallback in the CSS below.
	 *
	 * Transition speed/easing and the Hover Trigger come from the Hover tab,
	 * exactly as for hand-made styles.
	 */
	private function presets() {
		return [
			'gradient-border' => [
				'label'  => esc_html__( 'Gradient Border', 'webcasata-elementor-addons' ),
				'normal' => 'top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; box-sizing: border-box; padding: var(--wpe-size, 2px); border-radius: inherit; background: linear-gradient(135deg, var(--wpe-c1, #63b31e), var(--wpe-c2, #1e90ff)); -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); mask-composite: exclude;',
				'hover'  => '',
				'wrapper' => '',
			],
			'overlay'         => [
				'label'  => esc_html__( 'Hover Overlay (fades in)', 'webcasata-elementor-addons' ),
				'normal' => 'top: 0; right: 0; bottom: 0; left: 0; width: auto; height: auto; border-radius: inherit; background: var(--wpe-c1, rgba(0, 0, 0, 0.5)); opacity: 0; z-index: -1;',
				'hover'  => 'opacity: 1;',
				'wrapper' => 'isolation: isolate;',
			],
			'slide-fill'      => [
				'label'  => esc_html__( 'Slide-In Fill', 'webcasata-elementor-addons' ),
				'normal' => 'top: 0; left: 0; right: auto; bottom: auto; width: 100%; height: 100%; border-radius: inherit; background: var(--wpe-c1, #000000); z-index: -1; transform: translate(var(--wpe-fx, 0%), var(--wpe-fy, 100%));',
				'hover'  => 'transform: translate(0%, 0%);',
				'wrapper' => 'overflow: hidden; isolation: isolate;',
			],
			'ribbon'          => [
				'label'  => esc_html__( 'Corner Ribbon', 'webcasata-elementor-addons' ),
				'normal' => 'width: 150px; height: auto; top: 22px; bottom: auto; box-sizing: border-box; padding: 6px 0; background: var(--wpe-c1, #e63946); color: var(--wpe-c2, #ffffff); text-align: center; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; line-height: 1.4; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25); border-radius: 0;',
				'hover'  => '',
				'wrapper' => 'overflow: hidden;',
			],
			'underline'       => [
				'label'  => esc_html__( 'Animated Underline', 'webcasata-elementor-addons' ),
				'normal' => 'left: 0; bottom: 0; top: auto; right: auto; width: 0; height: var(--wpe-size, 3px); border-radius: 0; background: var(--wpe-c1, #63b31e);',
				'hover'  => 'width: 100%;',
				'wrapper' => '',
			],
		];
	}

	private function preset_controls( $element, $pseudo ) {
		$preset_id = $this->id( $pseudo, 'preset' );
		$options   = [ '' => esc_html__( 'None', 'webcasata-elementor-addons' ) ];
		foreach ( $this->presets() as $slug => $def ) {
			$options[ $slug ] = $def['label'];
		}

		$this->control(
			$element,
			$pseudo,
			'normal',
			'preset',
			[
				'label'       => esc_html__( 'Style Preset', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $options,
				'description' => esc_html__( 'A ready-made starting point. Anything you set in the Normal / Hover tabs below still overrides it.', 'webcasata-elementor-addons' ),
			]
		);

		// One hidden control per preset carries its CSS; it only exists while that preset is chosen.
		foreach ( $this->presets() as $slug => $def ) {
			$selectors = [ '{{SEL}}' => $def['normal'] ];
			if ( '' !== $def['hover'] ) {
				$selectors['{{HOVER}}'] = $def['hover'];
			}
			if ( '' !== $def['wrapper'] ) {
				$selectors['{{WRAPPER}}'] = $def['wrapper'];
			}

			$this->control(
				$element,
				$pseudo,
				'normal',
				'pre_' . str_replace( '-', '_', $slug ),
				[
					'type'      => Controls_Manager::HIDDEN,
					'default'   => 'yes',
					'condition' => [ $preset_id => $slug ],
					'selectors' => $selectors,
				]
			);
		}

		$all = array_keys( $this->presets() );

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_c1',
			[
				'label'       => esc_html__( 'Preset Color', 'webcasata-elementor-addons' ),
				'description' => esc_html__( 'Leave empty for the preset\'s own default.', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => [ '{{SEL}}' => '--wpe-c1: {{VALUE}};' ],
				'condition'   => [ $preset_id => $all ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_c2',
			[
				'label'       => esc_html__( 'Preset Color 2', 'webcasata-elementor-addons' ),
				'description' => esc_html__( 'Gradient Border: the second gradient colour. Corner Ribbon: the text colour.', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => [ '{{SEL}}' => '--wpe-c2: {{VALUE}};' ],
				'condition'   => [ $preset_id => [ 'gradient-border', 'ribbon' ] ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_size',
			[
				'label'      => esc_html__( 'Border Width / Thickness', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 1, 'max' => 40 ] ],
				'selectors'  => [ '{{SEL}}' => '--wpe-size: {{SIZE}}{{UNIT}};' ],
				'condition'  => [ $preset_id => [ 'gradient-border', 'underline' ] ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_dir',
			[
				'label'                => esc_html__( 'Slide In From', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'bottom',
				'options'              => [
					'bottom' => esc_html__( 'Bottom', 'webcasata-elementor-addons' ),
					'top'    => esc_html__( 'Top', 'webcasata-elementor-addons' ),
					'left'   => esc_html__( 'Left', 'webcasata-elementor-addons' ),
					'right'  => esc_html__( 'Right', 'webcasata-elementor-addons' ),
				],
				'selectors_dictionary' => [
					'bottom' => '--wpe-fx: 0%; --wpe-fy: 100%;',
					'top'    => '--wpe-fx: 0%; --wpe-fy: -100%;',
					'left'   => '--wpe-fx: -100%; --wpe-fy: 0%;',
					'right'  => '--wpe-fx: 100%; --wpe-fy: 0%;',
				],
				'selectors'            => [ '{{SEL}}' => '{{VALUE}}' ],
				'condition'            => [ $preset_id => 'slide-fill' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_corner',
			[
				'label'                => esc_html__( 'Ribbon Corner', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'top-right',
				'options'              => [
					'top-right' => esc_html__( 'Top right', 'webcasata-elementor-addons' ),
					'top-left'  => esc_html__( 'Top left', 'webcasata-elementor-addons' ),
				],
				'selectors_dictionary' => [
					'top-right' => 'right: -40px; left: auto; transform: rotate(45deg);',
					'top-left'  => 'left: -40px; right: auto; transform: rotate(-45deg);',
				],
				'selectors'            => [ '{{SEL}}' => '{{VALUE}}' ],
				'condition'            => [ $preset_id => 'ribbon' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_text',
			[
				'label'     => esc_html__( 'Ribbon Text', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'NEW',
				'selectors' => [ '{{SEL}}' => 'content: "{{VALUE}}";' ],
				'condition' => [ $preset_id => 'ribbon' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pre_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Hover presets use the Hover Trigger and Transition from the Hover tab. Overlay, Slide-In Fill and Corner Ribbon also set the container to overflow/isolation as needed.', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [ $preset_id . '!' => '' ],
			]
		);
	}

	/* ------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	public function add_style_controls( $element, $args = [] ) {
		foreach ( $this->pseudos() as $pseudo ) {
			$element->start_controls_section(
				self::PREFIX . $pseudo . '_style_section',
				[
					'label'     => sprintf(
						/* translators: %s: before or after */
						esc_html__( 'Pseudo :%s', 'webcasata-elementor-addons' ),
						$pseudo
					),
					'tab'       => Controls_Manager::TAB_STYLE,
					'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
				]
			);

			$this->preset_controls( $element, $pseudo );

			$element->start_controls_tabs( $this->id( $pseudo, 'tabs' ) );

			$element->start_controls_tab(
				$this->id( $pseudo, 'tab_normal' ),
				[ 'label' => esc_html__( 'Normal', 'webcasata-elementor-addons' ) ]
			);
			$this->normal_controls( $element, $pseudo );
			$element->end_controls_tab();

			$element->start_controls_tab(
				$this->id( $pseudo, 'tab_hover' ),
				[ 'label' => esc_html__( 'Hover', 'webcasata-elementor-addons' ) ]
			);
			$this->hover_controls( $element, $pseudo );
			$element->end_controls_tab();

			$element->end_controls_tabs();
			$element->end_controls_section();
		}
	}

	/** Size/offset slider helper (responsive). */
	private function length_control( $element, $pseudo, $mode, $name, $label, $css_prop ) {
		$this->control(
			$element,
			$pseudo,
			$mode,
			$name,
			[
				'label'      => $label,
				'type'       => Controls_Manager::SLIDER,
				'size_units' => $this->length_units(),
				'range'      => $this->length_range(),
				'selectors'  => [
					'{{SEL}}' => $css_prop . ': {{SIZE}}{{UNIT}};',
				],
			],
			true
		);
	}

	/** Controls that exist in both Normal and Hover (everything animatable). */
	private function shared_controls( $element, $pseudo, $mode ) {
		$this->length_control( $element, $pseudo, $mode, 'width', esc_html__( 'Width', 'webcasata-elementor-addons' ), 'width' );
		$this->length_control( $element, $pseudo, $mode, 'height', esc_html__( 'Height', 'webcasata-elementor-addons' ), 'height' );

		$element->add_control(
			$this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . 'heading_offset' ),
			[
				'label'     => esc_html__( 'Offset', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);
		$this->length_control( $element, $pseudo, $mode, 'top', esc_html__( 'Top', 'webcasata-elementor-addons' ), 'top' );
		$this->length_control( $element, $pseudo, $mode, 'right', esc_html__( 'Right', 'webcasata-elementor-addons' ), 'right' );
		$this->length_control( $element, $pseudo, $mode, 'bottom', esc_html__( 'Bottom', 'webcasata-elementor-addons' ), 'bottom' );
		$this->length_control( $element, $pseudo, $mode, 'left', esc_html__( 'Left', 'webcasata-elementor-addons' ), 'left' );

		$element->add_control(
			$this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . 'heading_bg' ),
			[
				'label'     => esc_html__( 'Background', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);
		$this->group(
			$element,
			$pseudo,
			$mode,
			Group_Control_Background::class,
			'bg',
			[ 'types' => [ 'classic', 'gradient' ] ]
		);

		$this->control(
			$element,
			$pseudo,
			$mode,
			'color',
			[
				'label'     => esc_html__( 'Text / Icon Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{SEL}}' => 'color: {{VALUE}};' ],
				'separator' => 'before',
			]
		);

		$this->control(
			$element,
			$pseudo,
			$mode,
			'opacity',
			[
				'label'     => esc_html__( 'Opacity', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
				'selectors' => [ '{{SEL}}' => 'opacity: {{SIZE}};' ],
				'separator' => 'before',
			]
		);

		$element->add_control(
			$this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . 'heading_border' ),
			[
				'label'     => esc_html__( 'Border & Shadow', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);
		$this->group( $element, $pseudo, $mode, Group_Control_Border::class, 'border', [] );
		$this->control(
			$element,
			$pseudo,
			$mode,
			'radius',
			[
				'label'      => esc_html__( 'Border Radius', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					'{{SEL}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			],
			true
		);
		$this->group( $element, $pseudo, $mode, Group_Control_Box_Shadow::class, 'shadow', [] );

		$element->add_control(
			$this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . 'heading_fx' ),
			[
				'label'     => esc_html__( 'Effects', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);
		$this->group( $element, $pseudo, $mode, Group_Control_Css_Filter::class, 'css_filter', [] );
		$this->control(
			$element,
			$pseudo,
			$mode,
			'backdrop',
			[
				'label'     => esc_html__( 'Backdrop Blur (px)', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'selectors' => [
					'{{SEL}}' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$element->add_control(
			$this->id( $pseudo, ( 'hover' === $mode ? 'h_' : '' ) . 'heading_tf' ),
			[
				'label'     => esc_html__( 'Transform', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);
		// translate X/Y go through CSS variables so setting only one still works,
		// and so Hover can change X without losing Normal's Y.
		$translate = 'translate: var(--wpe-tx, 0px) var(--wpe-ty, 0px);';
		$this->control(
			$element,
			$pseudo,
			$mode,
			'tx',
			[
				'label'      => esc_html__( 'Translate X', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'vw' ],
				'range'      => [
					'px' => [ 'min' => -500, 'max' => 500 ],
					'%'  => [ 'min' => -200, 'max' => 200 ],
					'em' => [ 'min' => -50, 'max' => 50 ],
					'vw' => [ 'min' => -100, 'max' => 100 ],
				],
				'selectors'  => [ '{{SEL}}' => '--wpe-tx: {{SIZE}}{{UNIT}}; ' . $translate ],
			],
			true
		);
		$this->control(
			$element,
			$pseudo,
			$mode,
			'ty',
			[
				'label'      => esc_html__( 'Translate Y', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'vh' ],
				'range'      => [
					'px' => [ 'min' => -500, 'max' => 500 ],
					'%'  => [ 'min' => -200, 'max' => 200 ],
					'em' => [ 'min' => -50, 'max' => 50 ],
					'vh' => [ 'min' => -100, 'max' => 100 ],
				],
				'selectors'  => [ '{{SEL}}' => '--wpe-ty: {{SIZE}}{{UNIT}}; ' . $translate ],
			],
			true
		);
		$this->control(
			$element,
			$pseudo,
			$mode,
			'rotate',
			[
				'label'      => esc_html__( 'Rotate (deg)', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => -360, 'max' => 360 ] ],
				'selectors'  => [ '{{SEL}}' => 'rotate: {{SIZE}}deg;' ],
			]
		);
		$this->control(
			$element,
			$pseudo,
			$mode,
			'scale',
			[
				'label'      => esc_html__( 'Scale', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 3, 'step' => 0.05 ] ],
				'selectors'  => [ '{{SEL}}' => 'scale: {{SIZE}};' ],
			]
		);
	}

	private function normal_controls( $element, $pseudo ) {
		$this->control(
			$element,
			$pseudo,
			'normal',
			'note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => 'before' === $pseudo
					? esc_html__( 'Elementor draws its Background Overlay with :before too, so by default this layer already covers the whole container (with the container\'s rounded corners). Do not combine it with a Background Overlay — use :after for that. Set Width/Height/Offset to resize it.', 'webcasata-elementor-addons' )
					: esc_html__( 'This element is invisible until it has a size: set Width/Height, or switch on "Fill container".', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'content',
			[
				'label'       => esc_html__( 'Content (text)', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty for a shape', 'webcasata-elementor-addons' ),
				'description' => esc_html__( 'Optional. An icon-font code such as \f101 also works.', 'webcasata-elementor-addons' ),
				'selectors'   => [ '{{SEL}}' => 'content: "{{VALUE}}";' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'font',
			[
				'label'               => esc_html__( 'Icon Font', 'webcasata-elementor-addons' ),
				'description'         => esc_html__( 'Pick the font that the Content code belongs to. Without it an icon code shows as an empty box. Elementor\'s Font Awesome / eicons are loaded automatically.', 'webcasata-elementor-addons' ),
				'type'                => Controls_Manager::SELECT,
				'default'             => '',
				'options'             => [
					''           => esc_html__( 'None (normal text)', 'webcasata-elementor-addons' ),
					'fa-solid'   => esc_html__( 'Font Awesome — Solid', 'webcasata-elementor-addons' ),
					'fa-regular' => esc_html__( 'Font Awesome — Regular', 'webcasata-elementor-addons' ),
					'fa-brands'  => esc_html__( 'Font Awesome — Brands', 'webcasata-elementor-addons' ),
					'eicons'     => esc_html__( 'Elementor Icons (eicons)', 'webcasata-elementor-addons' ),
				],
				'selectors_dictionary' => [
					'fa-solid'   => 'font-family: "Font Awesome 5 Free", "Font Awesome 6 Free"; font-weight: 900; font-style: normal;',
					'fa-regular' => 'font-family: "Font Awesome 5 Free", "Font Awesome 6 Free"; font-weight: 400; font-style: normal;',
					'fa-brands'  => 'font-family: "Font Awesome 5 Brands", "Font Awesome 6 Brands"; font-weight: 400; font-style: normal;',
					'eicons'     => 'font-family: eicons; font-weight: 400; font-style: normal;',
				],
				'selectors'           => [ '{{SEL}}' => '{{VALUE}}' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'center',
			[
				'label'        => esc_html__( 'Center Content', 'webcasata-elementor-addons' ),
				'description'  => esc_html__( 'Centers the text/icon horizontally and vertically inside the element\'s Width and Height.', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors'    => [
					'{{SEL}}' => 'display: flex; align-items: center; justify-content: center; text-align: center; line-height: 1;',
				],
			]
		);

		$this->group(
			$element,
			$pseudo,
			'normal',
			Group_Control_Typography::class,
			'typography',
			[]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'position',
			[
				'label'     => esc_html__( 'Position', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'absolute',
				'options'   => [
					'absolute' => esc_html__( 'Absolute (overlay)', 'webcasata-elementor-addons' ),
					'relative' => esc_html__( 'Relative (in the flow)', 'webcasata-elementor-addons' ),
					'static'   => esc_html__( 'Static', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ '{{SEL}}' => 'position: {{VALUE}};' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'fill',
			[
				'label'        => esc_html__( 'Fill Container', 'webcasata-elementor-addons' ),
				'description'  => esc_html__( 'top/right/bottom/left: 0 — stretches the element over the whole container.', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors'    => [ '{{SEL}}' => 'top: 0; right: 0; bottom: 0; left: 0;' ],
			]
		);

		$this->shared_controls( $element, $pseudo, 'normal' );

		$element->add_control(
			$this->id( $pseudo, 'heading_layer' ),
			[
				'label'     => esc_html__( 'Layering', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'behind',
			[
				'label'        => esc_html__( 'Send Behind Container Content', 'webcasata-elementor-addons' ),
				'description'  => esc_html__( 'z-index: -1, and the container is isolated so the element stays inside it instead of vanishing behind the page.', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors'    => [
					'{{SEL}}'    => 'z-index: -1;',
					'{{WRAPPER}}' => 'isolation: isolate;',
				],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'zindex',
			[
				'label'     => esc_html__( 'Z-Index', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'selectors' => [ '{{SEL}}' => 'z-index: {{VALUE}};' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'blend',
			[
				'label'     => esc_html__( 'Blend Mode', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''            => esc_html__( 'Normal', 'webcasata-elementor-addons' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'color-burn'  => 'Color Burn',
					'hard-light'  => 'Hard Light',
					'soft-light'  => 'Soft Light',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'luminosity'  => 'Luminosity',
				],
				'selectors' => [ '{{SEL}}' => 'mix-blend-mode: {{VALUE}};' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'pointer',
			[
				'label'     => esc_html__( 'Pointer Events', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => [
					'none' => esc_html__( 'None (clicks pass through)', 'webcasata-elementor-addons' ),
					'auto' => esc_html__( 'Auto', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ '{{SEL}}' => 'pointer-events: {{VALUE}};' ],
			]
		);
	}

	private function hover_controls( $element, $pseudo ) {
		$trigger_id = $this->id( $pseudo, 'h_trigger' );

		$this->control(
			$element,
			$pseudo,
			'hover',
			'trigger',
			[
				'label'        => esc_html__( 'Hover Trigger', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'self',
				'options'      => [
					'self'   => esc_html__( 'This container', 'webcasata-elementor-addons' ),
					'parent' => esc_html__( 'Direct parent container', 'webcasata-elementor-addons' ),
					'custom' => esc_html__( 'Custom class', 'webcasata-elementor-addons' ),
				],
				// Puts .wpe-{pseudo}-trg-{value} on the container; the hover CSS is gated on it.
				'prefix_class' => 'wpe-' . $pseudo . '-trg-',
				// Also handed to assets/js/webcasata-pseudo-hover.js.
				'frontend_available' => true,
			]
		);

		$this->control(
			$element,
			$pseudo,
			'hover',
			'class',
			[
				'label'              => esc_html__( 'Trigger Class', 'webcasata-elementor-addons' ),
				'type'               => Controls_Manager::TEXT,
				'placeholder'        => 'my-card',
				'description'        => esc_html__( 'Class name without the dot. When the pointer is over the nearest ancestor with this class (or, failing that, any element on the page with it), the hover styles apply.', 'webcasata-elementor-addons' ),
				'frontend_available' => true,
				'condition'          => [ $trigger_id => 'custom' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'hover',
			'note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Only the options you change here are applied on hover. Hover does not exist on touch screens.', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->shared_controls( $element, $pseudo, 'hover' );

		$element->add_control(
			$this->id( $pseudo, 'h_heading_transition' ),
			[
				'label'     => esc_html__( 'Transition', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ $this->id( $pseudo, 'enable' ) => 'yes' ],
			]
		);

		// The transition belongs on the NORMAL state, so it also plays when the pointer leaves.
		$this->control(
			$element,
			$pseudo,
			'normal',
			'h_duration',
			[
				'label'     => esc_html__( 'Duration (s)', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [ 'size' => 0.3 ],
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 3, 'step' => 0.05 ] ],
				'selectors' => [ '{{SEL}}' => 'transition-property: all; transition-duration: {{SIZE}}s;' ],
			]
		);

		$this->control(
			$element,
			$pseudo,
			'normal',
			'h_easing',
			[
				'label'     => esc_html__( 'Easing', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ease',
				'options'   => [
					'ease'                           => 'Ease',
					'linear'                         => 'Linear',
					'ease-in'                        => 'Ease in',
					'ease-out'                       => 'Ease out',
					'ease-in-out'                    => 'Ease in-out',
					'cubic-bezier(0.34, 1.56, 0.64, 1)' => esc_html__( 'Springy', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ '{{SEL}}' => 'transition-timing-function: {{VALUE}};' ],
			]
		);
	}

	/* ------------------------------------------------------------------
	 * CSS safety
	 * ------------------------------------------------------------------ */

	/**
	 * `content: "{{VALUE}}"` is written straight into the stylesheet, so make
	 * sure whatever was typed cannot close the string or the <style> tag.
	 * Backslashes are kept: they are how icon-font codes (\f101) are written.
	 */
	public function escape_content_value( $value, $css_property = '', $matches = [], $control = [] ) {
		if ( ! is_string( $value ) || empty( $control['name'] ) ) {
			return $value;
		}
		if ( ! preg_match( '/^wpe_(before|after)_(content|pre_text)$/', $control['name'] ) ) {
			return $value;
		}

		$value = str_replace( [ "\r", "\n" ], ' ', $value );
		$value = preg_replace( '/\\\\*"/', '\\"', $value );   // any run of \ before " => a single \"
		$value = str_replace( '<', '\\3C ', $value );
		if ( preg_match( '/(\\\\+)$/', $value, $m ) && strlen( $m[1] ) % 2 === 1 ) {
			$value .= '\\'; // a lone trailing backslash would escape the closing quote
		}

		return $value;
	}
}

Webcasata_Pseudo_Elements::instance();
