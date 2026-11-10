<?php
/**
 * Webcasata Parent Hover — "Style On Parent Hover" for any widget or container (Phase 2).
 *
 * Adds a section to the Advanced tab of every widget and every Container. Turn
 * it on and the element can change (text colour, background, opacity, border,
 * shadow, filters, transform) while the pointer is over its PARENT, which is
 * the classic "card hover changes the heading and the paragraph" case that
 * normally needs custom CSS.
 *
 * Hover Trigger:
 *   - Direct parent container (default, pure CSS)
 *   - Custom class: the nearest ancestor with that class (small script,
 *     assets/js/webcasata-pseudo-hover.js, toggles .wph-hover on this element)
 *
 * Same Elementor facts as the pseudo-element extension: only {{ID}} / {{WRAPPER}}
 * are replaced in selector keys, every control carries its own `condition`
 * (a section condition does not stop CSS), and empty values emit no CSS.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

final class Webcasata_Parent_Hover {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Widgets: Elementor builds the Advanced tab once, on the "common" stack.
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'add_controls' ], 10, 2 );
		add_action( 'elementor/element/common-optimized/_section_style/after_section_end', [ $this, 'add_controls' ], 10, 2 );
		// Containers.
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'add_controls' ], 10, 2 );
	}

	/* ------------------------------------------------------------------
	 * Selector helpers
	 * ------------------------------------------------------------------ */

	/**
	 * Hover selector list for this element (or its descendants, via $suffix).
	 *
	 * The element carries .wph-trg-custom only when "Custom class" is chosen,
	 * so "parent" is simply "not custom". The doubled .elementor-element-{id}
	 * lifts specificity above Elementor's own widget rules (which would
	 * otherwise win, e.g. the heading colour).
	 */
	private function hover_list( $suffix = '' ) {
		$e = '.elementor-element-{{ID}}.elementor-element-{{ID}}';
		$p = ':not(.wph-trg-custom)';

		return [
			'.elementor-element:hover > ' . $e . $p . $suffix,
			'.elementor-element:hover > .e-con-inner > ' . $e . $p . $suffix,
			'.elementor-element:hover > .elementor-widget-wrap > ' . $e . $p . $suffix,
			'{{WRAPPER}}.wph-trg-custom.wph-hover' . $suffix,
		];
	}

	private function resolve_tokens( array $selectors ) {
		$map = [
			'{{SEL}}'      => implode( ', ', $this->hover_list( '' ) ),
			'{{SEL_TEXT}}' => implode( ', ', array_merge( $this->hover_list( '' ), $this->hover_list( ' *' ) ) ),
			'{{SEL_SVG}}'  => implode( ', ', array_merge( $this->hover_list( ' svg' ), $this->hover_list( ' svg *' ) ) ),
		];

		$out = [];
		foreach ( $selectors as $key => $css ) {
			$out[ strtr( $key, $map ) ] = $css;
		}
		return $out;
	}

	private function add( $element, $id, array $args, $responsive = false ) {
		$args['condition'] = array_merge(
			[ 'wph_enable' => 'yes' ],
			isset( $args['condition'] ) ? $args['condition'] : []
		);
		if ( ! empty( $args['selectors'] ) ) {
			$args['selectors'] = $this->resolve_tokens( $args['selectors'] );
		}

		if ( $responsive ) {
			$element->add_responsive_control( $id, $args );
		} else {
			$element->add_control( $id, $args );
		}
	}

	private function group( $element, $type, $name, array $args = [] ) {
		$args['name']      = $name;
		$args['selector']  = implode( ', ', $this->hover_list( '' ) );
		$args['condition'] = [ 'wph_enable' => 'yes' ];
		$element->add_group_control( $type::get_type(), $args );
	}

	private function heading( $element, $id, $label ) {
		$element->add_control(
			$id,
			[
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'wph_enable' => 'yes' ],
			]
		);
	}

	/* ------------------------------------------------------------------
	 * Controls
	 * ------------------------------------------------------------------ */

	public function add_controls( $element, $args = [] ) {
		// "common" and "common-optimized" can both fire on some setups.
		if ( ! empty( $element->get_controls( 'wph_enable' ) ) ) {
			return;
		}

		$element->start_controls_section(
			'wph_section',
			[
				'label' => esc_html__( 'Style On Parent Hover', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'wph_enable',
			[
				'label'              => esc_html__( 'Style On Parent Hover', 'webcasata-elementor-addons' ),
				'type'               => Controls_Manager::SWITCHER,
				'return_value'       => 'yes',
				'default'            => '',
				'frontend_available' => true,
				// Transitions live on the normal state so they also play when the pointer leaves.
				'selectors'          => [
					'{{WRAPPER}}' => 'transition-property: color, background-color, border-color, border-radius, box-shadow, opacity, transform, translate, rotate, scale, filter; transition-duration: var(--wph-dur, 0.3s); transition-timing-function: var(--wph-ease, ease);',
				],
			]
		);

		$this->add(
			$element,
			'wph_trigger',
			[
				'label'              => esc_html__( 'Hover Trigger', 'webcasata-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => '',
				'options'            => [
					''       => esc_html__( 'Direct parent container', 'webcasata-elementor-addons' ),
					'custom' => esc_html__( 'Custom class', 'webcasata-elementor-addons' ),
				],
				// Only "custom" renders a class (.wph-trg-custom); an empty value renders nothing.
				'prefix_class'       => 'wph-trg-',
				'frontend_available' => true,
			]
		);

		$this->add(
			$element,
			'wph_class',
			[
				'label'              => esc_html__( 'Trigger Class', 'webcasata-elementor-addons' ),
				'type'               => Controls_Manager::TEXT,
				'placeholder'        => 'my-card',
				'description'        => esc_html__( 'Class name without the dot. The styles apply while the pointer is over the nearest ancestor with this class (or, failing that, any element on the page with it).', 'webcasata-elementor-addons' ),
				'frontend_available' => true,
				'condition'          => [ 'wph_trigger' => 'custom' ],
			]
		);

		$this->add(
			$element,
			'wph_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Only what you set below is applied while the parent is hovered. Text Color also recolours everything inside this element (headings, paragraphs, links). Hover does not exist on touch screens.', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		/* ---- Colour ---- */
		$this->heading( $element, 'wph_h_color', esc_html__( 'Color', 'webcasata-elementor-addons' ) );

		$this->add(
			$element,
			'wph_color',
			[
				'label'     => esc_html__( 'Text Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{SEL_TEXT}}'      => 'color: {{VALUE}};',
					// Inner text needs its own transition (it has its own colour rule).
					'{{WRAPPER}} *'     => 'transition-property: color; transition-duration: var(--wph-dur, 0.3s); transition-timing-function: var(--wph-ease, ease);',
				],
			]
		);

		$this->add(
			$element,
			'wph_fill',
			[
				'label'     => esc_html__( 'Icon (SVG) Fill Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{SEL_SVG}}'       => 'fill: {{VALUE}};',
					'{{WRAPPER}} svg, {{WRAPPER}} svg *' => 'transition-property: fill; transition-duration: var(--wph-dur, 0.3s); transition-timing-function: var(--wph-ease, ease);',
				],
			]
		);

		/* ---- Background ---- */
		$this->heading( $element, 'wph_h_bg', esc_html__( 'Background', 'webcasata-elementor-addons' ) );
		$this->group( $element, Group_Control_Background::class, 'wph_bg', [ 'types' => [ 'classic', 'gradient' ] ] );

		$this->add(
			$element,
			'wph_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
				'selectors' => [ '{{SEL}}' => 'opacity: {{SIZE}};' ],
				'separator' => 'before',
			]
		);

		/* ---- Border & shadow ---- */
		$this->heading( $element, 'wph_h_border', esc_html__( 'Border & Shadow', 'webcasata-elementor-addons' ) );
		$this->group( $element, Group_Control_Border::class, 'wph_border' );
		$this->add(
			$element,
			'wph_radius',
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
		$this->group( $element, Group_Control_Box_Shadow::class, 'wph_shadow' );

		/* ---- Effects ---- */
		$this->heading( $element, 'wph_h_fx', esc_html__( 'Effects', 'webcasata-elementor-addons' ) );
		$this->group( $element, Group_Control_Css_Filter::class, 'wph_css_filter' );

		/* ---- Transform ---- */
		$this->heading( $element, 'wph_h_tf', esc_html__( 'Transform', 'webcasata-elementor-addons' ) );
		$translate = 'translate: var(--wph-tx, 0px) var(--wph-ty, 0px);';
		$this->add(
			$element,
			'wph_tx',
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
				'selectors'  => [ '{{SEL}}' => '--wph-tx: {{SIZE}}{{UNIT}}; ' . $translate ],
			],
			true
		);
		$this->add(
			$element,
			'wph_ty',
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
				'selectors'  => [ '{{SEL}}' => '--wph-ty: {{SIZE}}{{UNIT}}; ' . $translate ],
			],
			true
		);
		$this->add(
			$element,
			'wph_rotate',
			[
				'label'      => esc_html__( 'Rotate (deg)', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => -360, 'max' => 360 ] ],
				'selectors'  => [ '{{SEL}}' => 'rotate: {{SIZE}}deg;' ],
			]
		);
		$this->add(
			$element,
			'wph_scale',
			[
				'label'      => esc_html__( 'Scale', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 3, 'step' => 0.05 ] ],
				'selectors'  => [ '{{SEL}}' => 'scale: {{SIZE}};' ],
			]
		);

		/* ---- Transition ---- */
		$this->heading( $element, 'wph_h_tr', esc_html__( 'Transition', 'webcasata-elementor-addons' ) );
		$this->add(
			$element,
			'wph_duration',
			[
				'label'     => esc_html__( 'Duration (s)', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [ 'size' => 0.3 ],
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 3, 'step' => 0.05 ] ],
				'selectors' => [ '{{WRAPPER}}' => '--wph-dur: {{SIZE}}s;' ],
			]
		);
		$this->add(
			$element,
			'wph_easing',
			[
				'label'     => esc_html__( 'Easing', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ease',
				'options'   => [
					'ease'                              => 'Ease',
					'linear'                            => 'Linear',
					'ease-in'                           => 'Ease in',
					'ease-out'                          => 'Ease out',
					'ease-in-out'                       => 'Ease in-out',
					'cubic-bezier(0.34, 1.56, 0.64, 1)' => esc_html__( 'Springy', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ '{{WRAPPER}}' => '--wph-ease: {{VALUE}};' ],
			]
		);

		$element->end_controls_section();
	}
}
