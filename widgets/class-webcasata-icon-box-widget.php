<?php
/**
 * Webcasata Icon Box
 *
 * An Icon Box with the things Elementor's own one lacks:
 *   - a real, fully styleable BACKGROUND around the icon (colour/gradient/image,
 *     opacity, border, radius, shadow, size) with Normal and Hover states;
 *   - hover styling that can be driven by the widget itself, its direct parent
 *     container, or any element with a custom class;
 *   - a "focused title": words written between {{ and }} are styled separately;
 *   - text fill (solid / gradient / image), text stroke and text shadow;
 *   - a title separator;
 *   - :before / :after pseudo-elements on the box (same feature as the Container).
 *
 * The markup and CSS are the widget's own (.wib-*), so nothing depends on
 * Elementor's icon-box stylesheet.
 *
 * Elementor behaviours this relies on (checked against its source): only
 * {{ID}} / {{WRAPPER}} are replaced in selector keys, so the hover trigger is a
 * class added by `prefix_class` that each hover selector is gated on; a
 * section's `condition` does not stop CSS, so controls carry their own.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

class Webcasata_Icon_Box_Widget extends Widget_Base {

	public function get_name() {
		return 'webcasata-icon-box';
	}

	public function get_title() {
		return __( 'Webcasata Icon Box', 'webcasata-elementor-addons' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	public function get_categories() {
		return [ 'webcasata' ];
	}

	public function get_keywords() {
		return [ 'icon', 'box', 'feature', 'heading', 'gradient', 'stroke', 'webcasata' ];
	}

	public function get_style_depends() {
		return [ 'webcasata-icon-box' ];
	}

	/* ==================================================================
	 * Selector helpers
	 * ================================================================== */

	/**
	 * Hover selector list for an inner part ($suffix, e.g. ' .wib-icon-bg').
	 * One entry per Hover Trigger, each gated by the class that the trigger
	 * select puts on the widget wrapper. The doubled .elementor-element-{id}
	 * and the gate lift specificity above the Normal rules.
	 */
	private function H( $suffix = '' ) {
		$e = '.elementor-element-{{ID}}.elementor-element-{{ID}}';

		return implode(
			', ',
			[
				'{{WRAPPER}}.wib-trg-self:hover' . $suffix,
				'.elementor-element:hover > ' . $e . '.wib-trg-parent' . $suffix,
				'.elementor-element:hover > .e-con-inner > ' . $e . '.wib-trg-parent' . $suffix,
				'.elementor-element:hover > .elementor-widget-wrap > ' . $e . '.wib-trg-parent' . $suffix,
				'{{WRAPPER}}.wib-trg-custom.wib-hover' . $suffix,
			]
		);
	}

	private function W( $suffix = '' ) {
		return '{{WRAPPER}}' . $suffix;
	}

	private function units() {
		return [ 'px', '%', 'em', 'rem', 'vw', 'custom' ];
	}

	/* ==================================================================
	 * Controls
	 * ================================================================== */

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_separator_content();

		$this->register_style_hover_trigger();
		$this->register_style_box();
		$this->register_style_icon();
		$this->register_style_icon_background();
		$this->register_style_title();
		$this->register_style_focus();
		$this->register_style_separator();
		$this->register_style_description();

		// :before / :after on the box (Content tab switches + Style blocks).
		if ( class_exists( 'Webcasata_Pseudo_Elements' ) ) {
			\Webcasata_Pseudo_Elements::instance()->register_on_widget( $this, ' .wib-wrapper' );
		}
	}

	/* ------------------------------------------------------------------
	 * Content tab
	 * ------------------------------------------------------------------ */

	private function register_content_controls() {
		$this->start_controls_section(
			'section_icon_box',
			[
				'label' => __( 'Icon Box', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'   => __( 'Icon', 'webcasata-elementor-addons' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'title_text',
			[
				'label'       => __( 'Title', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => __( 'This is the {{heading}}', 'webcasata-elementor-addons' ),
				'placeholder' => __( 'Enter your title', 'webcasata-elementor-addons' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'title_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Put words between <code>&#123;&#123;</code> and <code>&#125;&#125;</code> to style them separately (Style tab → Focused Title). Example: <code>We build &#123;&#123;lasting&#125;&#125; things</code>.', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'description_text',
			[
				'label'       => __( 'Description', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'default'     => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'webcasata-elementor-addons' ),
				'placeholder' => __( 'Enter your description', 'webcasata-elementor-addons' ),
				'rows'        => 6,
			]
		);

		$this->add_control(
			'link',
			[
				'label'     => __( 'Link', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => [ 'active' => true ],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_size',
			[
				'label'   => __( 'Title HTML Tag', 'webcasata-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default' => 'h3',
			]
		);

		$this->end_controls_section();
	}

	private function register_separator_content() {
		$this->start_controls_section(
			'section_separator',
			[
				'label' => __( 'Title Separator', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'sep_show',
			[
				'label'        => __( 'Show Separator', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'sep_position',
			[
				'label'     => __( 'Position', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => [
					'after'  => __( 'Below the title', 'webcasata-elementor-addons' ),
					'before' => __( 'Above the title', 'webcasata-elementor-addons' ),
				],
				'condition' => [ 'sep_show' => 'yes' ],
			]
		);

		$this->add_control(
			'sep_style',
			[
				'label'     => __( 'Style', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => [
					'solid'  => __( 'Line', 'webcasata-elementor-addons' ),
					'dashed' => __( 'Dashed', 'webcasata-elementor-addons' ),
					'dotted' => __( 'Dotted', 'webcasata-elementor-addons' ),
					'double' => __( 'Double line', 'webcasata-elementor-addons' ),
					'icon'   => __( 'Line with icon', 'webcasata-elementor-addons' ),
				],
				'condition' => [ 'sep_show' => 'yes' ],
			]
		);

		$this->add_control(
			'sep_icon',
			[
				'label'     => __( 'Separator Icon', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				],
				'condition' => [
					'sep_show'  => 'yes',
					'sep_style' => 'icon',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style: hover trigger
	 * ------------------------------------------------------------------ */

	private function register_style_hover_trigger() {
		$this->start_controls_section(
			'style_hover_trigger',
			[
				'label' => __( 'Hover Trigger', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wib_trigger',
			[
				'label'              => __( 'Hover Styles Apply When Hovering', 'webcasata-elementor-addons' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'self',
				'options'            => [
					'self'   => __( 'This widget', 'webcasata-elementor-addons' ),
					'parent' => __( 'Direct parent container', 'webcasata-elementor-addons' ),
					'custom' => __( 'Custom class', 'webcasata-elementor-addons' ),
				],
				'description'        => __( 'Decides what has to be hovered for every Hover tab in this widget (icon, icon background, title, separator, description).', 'webcasata-elementor-addons' ),
				// Puts .wib-trg-{value} on the widget; every hover rule is gated on it.
				'prefix_class'       => 'wib-trg-',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'wib_trigger_class',
			[
				'label'              => __( 'Trigger Class', 'webcasata-elementor-addons' ),
				'type'               => Controls_Manager::TEXT,
				'placeholder'        => 'my-card',
				'description'        => __( 'Class name without the dot. The hover styles apply while the pointer is over the nearest ancestor with this class (or, failing that, any element on the page with it).', 'webcasata-elementor-addons' ),
				'frontend_available' => true,
				'condition'          => [ 'wib_trigger' => 'custom' ],
			]
		);

		$this->add_control(
			'wib_duration',
			[
				'label'     => __( 'Transition Duration (s)', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [ 'size' => 0.3 ],
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 3, 'step' => 0.05 ] ],
				'selectors' => [ $this->W() => '--wib-dur: {{SIZE}}s;' ],
			]
		);

		$this->add_control(
			'wib_easing',
			[
				'label'     => __( 'Transition Easing', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ease',
				'options'   => [
					'ease'                              => 'Ease',
					'linear'                            => 'Linear',
					'ease-in'                           => 'Ease in',
					'ease-out'                          => 'Ease out',
					'ease-in-out'                       => 'Ease in-out',
					'cubic-bezier(0.34, 1.56, 0.64, 1)' => __( 'Springy', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ $this->W() => '--wib-ease: {{VALUE}};' ],
			]
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style: box layout
	 * ------------------------------------------------------------------ */

	private function register_style_box() {
		$this->start_controls_section(
			'style_box',
			[
				'label' => __( 'Box', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'box_position',
			[
				'label'                => __( 'Icon Position', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::CHOOSE,
				'default'              => 'top',
				'mobile_default'       => 'top',
				'options'              => [
					'left'   => [ 'title' => __( 'Left', 'webcasata-elementor-addons' ), 'icon' => 'eicon-h-align-left' ],
					'top'    => [ 'title' => __( 'Top', 'webcasata-elementor-addons' ), 'icon' => 'eicon-v-align-top' ],
					'right'  => [ 'title' => __( 'Right', 'webcasata-elementor-addons' ), 'icon' => 'eicon-h-align-right' ],
					'bottom' => [ 'title' => __( 'Bottom', 'webcasata-elementor-addons' ), 'icon' => 'eicon-v-align-bottom' ],
				],
				// Each position sets direction AND cross-axis alignment, so mixing a row layout on
				// desktop with a column layout on mobile never inherits the wrong alignment.
				'selectors_dictionary' => [
					'top'    => 'flex-direction: column; align-items: stretch;',
					'bottom' => 'flex-direction: column-reverse; align-items: stretch;',
					'left'   => 'flex-direction: row; align-items: var(--wib-va, flex-start);',
					'right'  => 'flex-direction: row-reverse; align-items: var(--wib-va, flex-start);',
				],
				'selectors'            => [ $this->W( ' .wib-wrapper' ) => '{{VALUE}}' ],
			]
		);

		$this->add_responsive_control(
			'box_valign',
			[
				'label'                => __( 'Vertical Alignment', 'webcasata-elementor-addons' ),
				'description'          => __( 'Used when the icon is on the left or right.', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
					'top'    => [ 'title' => __( 'Top', 'webcasata-elementor-addons' ), 'icon' => 'eicon-v-align-top' ],
					'middle' => [ 'title' => __( 'Middle', 'webcasata-elementor-addons' ), 'icon' => 'eicon-v-align-middle' ],
					'bottom' => [ 'title' => __( 'Bottom', 'webcasata-elementor-addons' ), 'icon' => 'eicon-v-align-bottom' ],
				],
				'selectors_dictionary' => [
					'top'    => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors'            => [ $this->W( ' .wib-wrapper' ) => '--wib-va: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'box_align',
			[
				'label'     => __( 'Alignment', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'start'  => [ 'title' => __( 'Start', 'webcasata-elementor-addons' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => __( 'Center', 'webcasata-elementor-addons' ), 'icon' => 'eicon-text-align-center' ],
					'end'    => [ 'title' => __( 'End', 'webcasata-elementor-addons' ), 'icon' => 'eicon-text-align-right' ],
				],
				// start / center / end are valid for text-align, justify-content and align-items alike.
				'selectors' => [
					$this->W( ' .wib-wrapper' )   => 'text-align: {{VALUE}};',
					$this->W( ' .wib-icon-wrap' ) => 'justify-content: {{VALUE}};',
					$this->W( ' .wib-content' )   => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'box_gap',
			[
				'label'      => __( 'Icon Spacing', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => $this->units(),
				'default'    => [ 'size' => 15, 'unit' => 'px' ],
				'range'      => [
					'px' => [ 'max' => 100 ],
					'em' => [ 'max' => 10 ],
				],
				'selectors'  => [ $this->W( ' .wib-wrapper' ) => 'gap: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style: icon
	 * ------------------------------------------------------------------ */

	private function register_style_icon() {
		$this->start_controls_section(
			'style_icon',
			[
				'label' => __( 'Icon', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => __( 'Size', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => $this->units(),
				'range'      => [ 'px' => [ 'min' => 6, 'max' => 300 ] ],
				'selectors'  => [ $this->W( ' .wib-icon' ) => 'font-size: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->start_controls_tabs( 'icon_colors' );

		$this->start_controls_tab( 'icon_colors_normal', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wib-icon' ) => 'color: {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'icon_colors_hover', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'icon_color_h',
			[
				'label'     => __( 'Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->H( ' .wib-icon' ) => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'icon_anim_h',
			[
				'label'                => __( 'Hover Animation', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => '',
				'options'              => [
					''       => __( 'None', 'webcasata-elementor-addons' ),
					'grow'   => __( 'Grow', 'webcasata-elementor-addons' ),
					'shrink' => __( 'Shrink', 'webcasata-elementor-addons' ),
					'float'  => __( 'Float up', 'webcasata-elementor-addons' ),
					'sink'   => __( 'Sink down', 'webcasata-elementor-addons' ),
					'rotate' => __( 'Rotate', 'webcasata-elementor-addons' ),
					'tilt'   => __( 'Tilt', 'webcasata-elementor-addons' ),
				],
				'selectors_dictionary' => [
					'grow'   => 'transform: scale(1.12);',
					'shrink' => 'transform: scale(0.9);',
					'float'  => 'transform: translateY(-6px);',
					'sink'   => 'transform: translateY(6px);',
					'rotate' => 'transform: rotate(12deg);',
					'tilt'   => 'transform: rotate(-12deg) scale(1.05);',
				],
				'selectors'            => [ $this->H( ' .wib-icon' ) => '{{VALUE}}' ],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'icon_rotate',
			[
				'label'      => __( 'Rotate (deg)', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => -360, 'max' => 360 ] ],
				'selectors'  => [ $this->W( ' .wib-icon-glyph' ) => 'transform: rotate({{SIZE}}deg);' ],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style: icon background (the main missing feature)
	 * ------------------------------------------------------------------ */

	private function register_style_icon_background() {
		$this->start_controls_section(
			'style_icon_bg',
			[
				'label' => __( 'Icon Background', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Set a Background (Normal tab) to draw a shape behind the icon. Use Size, or Padding, to make room around the icon.', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'bg_shape',
			[
				'label'                => __( 'Shape', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => '',
				'options'              => [
					''        => __( 'Custom (use Border Radius)', 'webcasata-elementor-addons' ),
					'circle'  => __( 'Circle', 'webcasata-elementor-addons' ),
					'rounded' => __( 'Rounded', 'webcasata-elementor-addons' ),
					'square'  => __( 'Square', 'webcasata-elementor-addons' ),
				],
				'selectors_dictionary' => [
					'circle'  => 'border-radius: 50%;',
					'rounded' => 'border-radius: 16px;',
					'square'  => 'border-radius: 0;',
				],
				'selectors'            => [ $this->W( ' .wib-icon-bg' ) => '{{VALUE}}' ],
			]
		);

		$this->add_responsive_control(
			'bg_box_size',
			[
				'label'       => __( 'Size (width & height)', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', 'em', 'rem', 'vw', 'custom' ],
				'range'       => [ 'px' => [ 'min' => 10, 'max' => 400 ] ],
				'selectors'   => [ $this->W( ' .wib-icon' ) => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'bg_padding',
			[
				'label'      => __( 'Padding', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%', 'custom' ],
				'selectors'  => [
					$this->W( ' .wib-icon' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'bg_tabs' );

		$this->start_controls_tab( 'bg_tab_normal', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->icon_bg_state_controls( 'bg', $this->W( ' .wib-icon-bg' ) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'bg_tab_hover', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->icon_bg_state_controls( 'bg_h', $this->H( ' .wib-icon-bg' ) );
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	/** Background / opacity / border / radius / shadow for one state ($p = control prefix, $sel = selector list). */
	private function icon_bg_state_controls( $p, $sel ) {
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => $p,
				'types'    => [ 'classic', 'gradient' ],
				'selector' => $sel,
			]
		);

		$this->add_control(
			$p . '_opacity',
			[
				'label'     => __( 'Background Opacity', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ] ],
				'selectors' => [ $sel => 'opacity: {{SIZE}};' ],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => $p . '_border',
				'selector'  => $sel,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			$p . '_radius',
			[
				'label'      => __( 'Border Radius', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					$sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => $p . '_shadow',
				'selector' => $sel,
			]
		);
	}

	/* ------------------------------------------------------------------
	 * Style: text blocks (Title and Focused Title share the same toolkit)
	 * ------------------------------------------------------------------ */

	/**
	 * Typography, Text Fill (Solid / Gradient / Image), Stroke and Shadow for
	 * the text found at $part (e.g. ' .wib-title').
	 *
	 * @param string $key  Control prefix: 't' (title) or 'f' (focus).
	 * @param string $part Selector part appended to the wrapper.
	 * @param string $typo_selector Selector list for the Typography group.
	 */
	private function text_toolkit( $key, $part, $typo_selector ) {
		$W    = $this->W( $part );
		$gate = '.wib-' . $key . 'fill-'; // class added by the fill select, e.g. .wib-tfill-gradient
		$hid  = function ( $n ) use ( $key ) {
			return $key . '_' . $n;
		};

		/* -- Typography -- */
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => $hid( 'typography' ),
				'selector' => $typo_selector,
			]
		);

		/* -- Text fill -- */
		$this->add_control(
			$hid( 'fill_heading' ),
			[
				'label'     => __( 'Text Fill', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$clip = '-webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; text-shadow: none;';
		$this->add_control(
			$hid( 'fill' ),
			[
				'label'                => __( 'Fill Type', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'solid',
				'options'              => [
					'solid'    => __( 'Solid', 'webcasata-elementor-addons' ),
					'gradient' => __( 'Gradient', 'webcasata-elementor-addons' ),
					'image'    => __( 'Image', 'webcasata-elementor-addons' ),
				],
				// Gradient / image text = the background clipped to the letters.
				'selectors_dictionary' => [
					'solid'    => '',
					'gradient' => $clip,
					'image'    => $clip,
				],
				'selectors'            => [ $W => '{{VALUE}}' ],
				// .wib-tfill-solid / -gradient / -image on the widget (used to pick the shadow technique).
				'prefix_class'         => 'wib-' . $key . 'fill-',
			]
		);

		// Solid: Normal + Hover colour.
		$this->start_controls_tabs( $hid( 'color_tabs' ), [ 'condition' => [ $hid( 'fill' ) => 'solid' ] ] );
		$this->start_controls_tab( $hid( 'color_normal' ), [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			$hid( 'color' ),
			[
				'label'     => __( 'Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $W => 'color: {{VALUE}}; -webkit-text-fill-color: {{VALUE}};' ],
				'condition' => [ $hid( 'fill' ) => 'solid' ],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( $hid( 'color_hover' ), [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			$hid( 'color_h' ),
			[
				'label'     => __( 'Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->H( $part ) => 'color: {{VALUE}}; -webkit-text-fill-color: {{VALUE}};' ],
				'condition' => [ $hid( 'fill' ) => 'solid' ],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Gradient.
		$this->add_control(
			$hid( 'g1' ),
			[
				'label'     => __( 'Gradient: Color A', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#63b31e',
				'condition' => [ $hid( 'fill' ) => 'gradient' ],
			]
		);
		$this->add_control(
			$hid( 'g2' ),
			[
				'label'     => __( 'Gradient: Color B', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e90ff',
				'condition' => [ $hid( 'fill' ) => 'gradient' ],
			]
		);
		$this->add_control(
			$hid( 'gang' ),
			[
				'label'      => __( 'Gradient: Angle', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [ 'deg' => [ 'min' => 0, 'max' => 360 ] ],
				'default'    => [ 'size' => 90, 'unit' => 'deg' ],
				'selectors'  => [
					$W => 'background-image: linear-gradient({{SIZE}}{{UNIT}}, {{' . $hid( 'g1' ) . '.VALUE}}, {{' . $hid( 'g2' ) . '.VALUE}});',
				],
				'condition'  => [ $hid( 'fill' ) => 'gradient' ],
			]
		);

		// Image.
		$this->add_control(
			$hid( 'img' ),
			[
				'label'     => __( 'Fill Image', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'selectors' => [ $W => 'background-image: url("{{URL}}");' ],
				'condition' => [ $hid( 'fill' ) => 'image' ],
			]
		);
		$this->add_control(
			$hid( 'img_size' ),
			[
				'label'     => __( 'Image Size', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => [
					'cover'   => __( 'Cover', 'webcasata-elementor-addons' ),
					'contain' => __( 'Contain', 'webcasata-elementor-addons' ),
					'auto'    => __( 'Auto', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ $W => 'background-size: {{VALUE}};' ],
				'condition' => [ $hid( 'fill' ) => 'image' ],
			]
		);
		$this->add_control(
			$hid( 'img_pos' ),
			[
				'label'     => __( 'Image Position', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center center',
				'options'   => [
					'center center' => __( 'Center', 'webcasata-elementor-addons' ),
					'top center'    => __( 'Top', 'webcasata-elementor-addons' ),
					'bottom center' => __( 'Bottom', 'webcasata-elementor-addons' ),
					'center left'   => __( 'Left', 'webcasata-elementor-addons' ),
					'center right'  => __( 'Right', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ $W => 'background-position: {{VALUE}};' ],
				'condition' => [ $hid( 'fill' ) => 'image' ],
			]
		);
		$this->add_control(
			$hid( 'img_rep' ),
			[
				'label'     => __( 'Image Repeat', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'no-repeat',
				'options'   => [
					'no-repeat' => __( 'No repeat', 'webcasata-elementor-addons' ),
					'repeat'    => __( 'Repeat', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ $W => 'background-repeat: {{VALUE}};' ],
				'condition' => [ $hid( 'fill' ) => 'image' ],
			]
		);

		/* -- Stroke -- */
		$this->add_control(
			$hid( 'stroke_heading' ),
			[
				'label'     => __( 'Text Stroke', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$this->add_control(
			$hid( 'stroke_w' ),
			[
				'label'      => __( 'Stroke Width', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 20, 'step' => 0.5 ] ],
				'selectors'  => [ $W => '-webkit-text-stroke-width: {{SIZE}}{{UNIT}};' ],
			]
		);
		$this->add_control(
			$hid( 'stroke_c' ),
			[
				'label'     => __( 'Stroke Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $W => '-webkit-text-stroke-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			$hid( 'hollow' ),
			[
				'label'        => __( 'Outline Only (transparent fill)', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors'    => [ $W => 'color: transparent; -webkit-text-fill-color: transparent;' ],
			]
		);
		$this->add_control(
			$hid( 'behind' ),
			[
				'label'        => __( 'Stroke Behind Fill', 'webcasata-elementor-addons' ),
				'description'  => __( 'Draws the stroke underneath the letters, so thick strokes do not eat into them.', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'selectors'    => [ $W => 'paint-order: stroke fill;' ],
			]
		);

		/* -- Shadow -- */
		$this->add_control(
			$hid( 'shadow_heading' ),
			[
				'label'     => __( 'Text Shadow', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$vx = '--wib-' . $key . '-x';
		$vy = '--wib-' . $key . '-y';
		$vb = '--wib-' . $key . '-b';
		$sh = 'var(' . $vx . ', 0px) var(' . $vy . ', 0px) var(' . $vb . ', 0px) {{VALUE}}';

		$this->add_control(
			$hid( 'sh_c' ),
			[
				'label'       => __( 'Shadow Color', 'webcasata-elementor-addons' ),
				'description' => __( 'Pick a color to switch the shadow on.', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::COLOR,
				// text-shadow shows THROUGH a gradient/image fill (the letters are transparent),
				// so those fills use drop-shadow() instead, which follows the painted letters.
				'selectors'   => [
					'{{WRAPPER}}:not(' . $gate . 'gradient):not(' . $gate . 'image)' . $part => 'text-shadow: ' . $sh . ';',
					'{{WRAPPER}}' . $gate . 'gradient' . $part . ', {{WRAPPER}}' . $gate . 'image' . $part => 'filter: drop-shadow(' . $sh . ');',
				],
			]
		);
		foreach ( [ 'x' => [ $vx, __( 'Shadow Horizontal', 'webcasata-elementor-addons' ) ], 'y' => [ $vy, __( 'Shadow Vertical', 'webcasata-elementor-addons' ) ], 'b' => [ $vb, __( 'Shadow Blur', 'webcasata-elementor-addons' ) ] ] as $n => $def ) {
			$this->add_control(
				$hid( 'sh_' . $n ),
				[
					'label'      => $def[1],
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range'      => [ 'px' => [ 'min' => 'b' === $n ? 0 : -50, 'max' => 50 ] ],
					'selectors'  => [ $W => $def[0] . ': {{SIZE}}{{UNIT}};' ],
				]
			);
		}
	}

	private function register_style_title() {
		$this->start_controls_section(
			'style_title',
			[
				'label' => __( 'Title', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'title_space',
			[
				'label'      => __( 'Space Below Title', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range'      => [ 'px' => [ 'max' => 100 ], 'em' => [ 'max' => 10 ] ],
				'selectors'  => [ $this->W( ' .wib-title' ) => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->text_toolkit(
			't',
			' .wib-title',
			$this->W( ' .wib-title' ) . ', ' . $this->W( ' .wib-title a' )
		);

		$this->end_controls_section();
	}

	private function register_style_focus() {
		$this->start_controls_section(
			'style_focus',
			[
				'label' => __( 'Focused Title', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'focus_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Styles the words you wrapped in <code>&#123;&#123; &#125;&#125;</code> in the Title. Anything you leave empty is inherited from the Title.', 'webcasata-elementor-addons' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'focus_display',
			[
				'label'     => __( 'Display', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inline',
				'options'   => [
					'inline' => __( 'Inline (same line)', 'webcasata-elementor-addons' ),
					'block'  => __( 'On its own line', 'webcasata-elementor-addons' ),
				],
				'selectors' => [ $this->W( ' .wib-focus' ) => 'display: {{VALUE}};' ],
			]
		);

		$this->text_toolkit( 'f', ' .wib-focus', $this->W( ' .wib-focus' ) );

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style: separator
	 * ------------------------------------------------------------------ */

	private function register_style_separator() {
		$on = [ 'sep_show' => 'yes' ];

		$this->start_controls_section(
			'style_separator',
			[
				'label'     => __( 'Title Separator', 'webcasata-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => $on,
			]
		);

		$this->add_responsive_control(
			'sep_width',
			[
				'label'      => __( 'Width', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 600 ], '%' => [ 'min' => 5, 'max' => 100 ] ],
				'selectors'  => [ $this->W( ' .wib-sep' ) => 'width: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->add_control(
			'sep_thickness',
			[
				'label'      => __( 'Thickness', 'webcasata-elementor-addons' ),
				'description' => __( 'The Double style needs at least 3px to show two lines.', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 1, 'max' => 20 ] ],
				'selectors'  => [ $this->W( ' .wib-sep' ) => '--wib-sep-t: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->start_controls_tabs( 'sep_colors', [ 'condition' => $on ] );
		$this->start_controls_tab( 'sep_colors_n', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'sep_color',
			[
				'label'     => __( 'Line Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wib-sep' ) => '--wib-sep-c: {{VALUE}};' ],
				'condition' => $on,
			]
		);
		$this->add_control(
			'sep_icon_color',
			[
				'label'     => __( 'Icon Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wib-sep' ) => '--wib-sep-ic: {{VALUE}};' ],
				'condition' => $on + [ 'sep_style' => 'icon' ],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'sep_colors_h', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'sep_color_h',
			[
				'label'     => __( 'Line Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->H( ' .wib-sep' ) => '--wib-sep-c: {{VALUE}};' ],
				'condition' => $on,
			]
		);
		$this->add_control(
			'sep_icon_color_h',
			[
				'label'     => __( 'Icon Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->H( ' .wib-sep' ) => '--wib-sep-ic: {{VALUE}};' ],
				'condition' => $on + [ 'sep_style' => 'icon' ],
			]
		);
		$this->add_control(
			'sep_width_h',
			[
				'label'      => __( 'Width on Hover', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 600 ], '%' => [ 'min' => 5, 'max' => 100 ] ],
				'selectors'  => [ $this->H( ' .wib-sep' ) => 'width: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'sep_icon_size',
			[
				'label'      => __( 'Icon Size', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 6, 'max' => 80 ] ],
				'selectors'  => [ $this->W( ' .wib-sep' ) => '--wib-sep-isz: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on + [ 'sep_style' => 'icon' ],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'sep_space_top',
			[
				'label'      => __( 'Space Above', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [ 'px' => [ 'max' => 100 ] ],
				'selectors'  => [ $this->W( ' .wib-sep' ) => 'margin-top: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->add_responsive_control(
			'sep_space_bottom',
			[
				'label'      => __( 'Space Below', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [ 'px' => [ 'max' => 100 ] ],
				'selectors'  => [ $this->W( ' .wib-sep' ) => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style: description
	 * ------------------------------------------------------------------ */

	private function register_style_description() {
		$this->start_controls_section(
			'style_desc',
			[
				'label' => __( 'Description', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => $this->W( ' .wib-desc' ),
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'desc_shadow',
				'selector' => $this->W( ' .wib-desc' ),
			]
		);

		$this->start_controls_tabs( 'desc_colors' );
		$this->start_controls_tab( 'desc_colors_n', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'desc_color',
			[
				'label'     => __( 'Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wib-desc' ) => 'color: {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'desc_colors_h', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'desc_color_h',
			[
				'label'     => __( 'Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->H( ' .wib-desc' ) => 'color: {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/* ==================================================================
	 * Render
	 * ================================================================== */

	/**
	 * Title text -> safe HTML, with {{words}} turned into the focused span.
	 * Public so it can be unit-tested without Elementor.
	 */
	public function format_title( $raw ) {
		$safe = wp_kses_post( (string) $raw );

		return preg_replace_callback(
			'/\{\{\s*(.+?)\s*\}\}/s',
			function ( $m ) {
				return '<span class="wib-focus">' . $m[1] . '</span>';
			},
			$safe
		);
	}

	private function render_separator( $settings ) {
		$style = isset( $settings['sep_style'] ) ? $settings['sep_style'] : 'solid';
		if ( ! in_array( $style, [ 'solid', 'dashed', 'dotted', 'double', 'icon' ], true ) ) {
			$style = 'solid';
		}

		echo '<div class="wib-sep wib-sep-' . esc_attr( $style ) . '" aria-hidden="true"><span class="wib-sep-line"></span>';
		if ( 'icon' === $style && ! empty( $settings['sep_icon']['value'] ) ) {
			echo '<span class="wib-sep-icon">';
			Icons_Manager::render_icon( $settings['sep_icon'], [ 'aria-hidden' => 'true' ] );
			echo '</span><span class="wib-sep-line"></span>';
		}
		echo '</div>';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$has_link = ! empty( $settings['link']['url'] );
		$tag      = $has_link ? 'a' : 'span';
		$has_icon = ! empty( $settings['selected_icon']['value'] );
		$title    = isset( $settings['title_text'] ) ? (string) $settings['title_text'] : '';
		$desc     = isset( $settings['description_text'] ) ? (string) $settings['description_text'] : '';

		if ( ! $has_icon && '' === trim( $title ) && '' === trim( $desc ) ) {
			return;
		}

		$title_tag = isset( $settings['title_size'] ) ? $settings['title_size'] : 'h3';
		if ( ! in_array( $title_tag, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ], true ) ) {
			$title_tag = 'h3';
		}

		$this->add_render_attribute( 'icon', 'class', 'wib-icon' );
		if ( $has_link ) {
			$this->add_link_attributes( 'link', $settings['link'] );
			$this->add_render_attribute( 'icon', 'tabindex', '-1' );
			if ( '' !== trim( wp_strip_all_tags( $title ) ) ) {
				$this->add_render_attribute( 'icon', 'aria-label', wp_strip_all_tags( preg_replace( '/\{\{|\}\}/', '', $title ) ) );
			}
		}

		$show_sep   = 'yes' === ( isset( $settings['sep_show'] ) ? $settings['sep_show'] : '' );
		$sep_before = $show_sep && 'before' === ( isset( $settings['sep_position'] ) ? $settings['sep_position'] : 'after' );
		$sep_after  = $show_sep && ! $sep_before;
		?>
		<div class="wib-wrapper">
			<?php if ( $has_icon ) : ?>
				<div class="wib-icon-wrap">
					<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( 'link' ); ?> <?php $this->print_render_attribute_string( 'icon' ); ?>>
						<span class="wib-icon-bg" aria-hidden="true"></span>
						<span class="wib-icon-glyph"><?php Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?></span>
					</<?php echo esc_html( $tag ); ?>>
				</div>
			<?php endif; ?>

			<?php if ( '' !== trim( $title ) || '' !== trim( $desc ) || $show_sep ) : ?>
				<div class="wib-content">
					<?php
					if ( $sep_before ) {
						$this->render_separator( $settings );
					}
					if ( '' !== trim( $title ) ) :
						?>
						<<?php echo esc_html( $title_tag ); ?> class="wib-title">
							<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( 'link' ); ?>>
								<?php echo $this->format_title( $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitised by wp_kses_post in format_title(). ?>
							</<?php echo esc_html( $tag ); ?>>
						</<?php echo esc_html( $title_tag ); ?>>
						<?php
					endif;
					if ( $sep_after ) {
						$this->render_separator( $settings );
					}
					if ( '' !== trim( $desc ) ) :
						?>
						<p class="wib-desc"><?php echo wp_kses_post( $desc ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
