<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Utils;

class Qyrus_Home_Accordion_Widget extends Widget_Base {

	public function get_name() {
		return 'qyrus-home-accordion';
	}

	public function get_title() {
		return __( 'Webcasata Accordion', 'webcasata-elementor-addons' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_categories() {
		return [ 'webcasata' ];
	}

	public function get_keywords() {
		return [ 'accordion', 'toggle', 'image', 'tabs', 'faq', 'webcasata' ];
	}

	public function get_script_depends() {
		return [ 'qyrus-accordion' ];
	}

	public function get_style_depends() {
		return [ 'qyrus-accordion' ];
	}

	protected function register_controls() {
		$this->register_content_items_section();
		$this->register_content_settings_section();
		$this->register_style_layout_section();
		$this->register_style_title_section();
		$this->register_style_description_section();
		$this->register_style_button_section();
		$this->register_style_image_section();
	}

	/* -----------------------------------------------------------------
	 * CONTENT TAB
	 * --------------------------------------------------------------- */

	private function register_content_items_section() {
		$this->start_controls_section( 'section_items', [
			'label' => __( 'Accordion Items', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'item_title', [
			'label'       => __( 'Title', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Accordion Title', 'webcasata-elementor-addons' ),
			'label_block' => true,
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'item_description', [
			'label'       => __( 'Description', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => __( 'Add a short description for this accordion item here.', 'webcasata-elementor-addons' ),
			'label_block' => true,
			'rows'        => 5,
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'item_image', [
			'label'   => __( 'Image', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::MEDIA,
			'dynamic' => [ 'active' => true ],
			'default' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		] );

		$repeater->add_control( 'read_more_text', [
			'label'       => __( 'Read More Text', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Read more', 'webcasata-elementor-addons' ),
			'label_block' => true,
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'read_more_link', [
			'label'         => __( 'Read More Link', 'webcasata-elementor-addons' ),
			'type'          => Controls_Manager::URL,
			'placeholder'   => __( 'https://your-link.com', 'webcasata-elementor-addons' ),
			'show_external' => true,
			'default'       => [
				'url'         => '#',
				'is_external' => false,
				'nofollow'    => false,
			],
			'dynamic'       => [ 'active' => true ],
		] );

		$this->add_control( 'items', [
			'label'       => __( 'Items', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ item_title }}}',
			'default'     => [
				[
					'item_title'       => __( 'Industry-First Agentic SEER Framework', 'webcasata-elementor-addons' ),
					'item_description' => __( 'Sense → Evaluate → Execute → Report – an autonomous AI-driven test orchestration framework that intelligently manages testing workflows.', 'webcasata-elementor-addons' ),
					'read_more_text'   => __( 'Read more', 'webcasata-elementor-addons' ),
				],
				[
					'item_title'       => __( 'Simplified Web App Testing', 'webcasata-elementor-addons' ),
					'item_description' => __( 'Expedite your web testing with our low-code/no-code platform, featuring an AI-powered test recorder, data management tools, and automated script healing.', 'webcasata-elementor-addons' ),
					'read_more_text'   => __( 'Read more', 'webcasata-elementor-addons' ),
				],
				[
					'item_title'       => __( 'Next-Generation Mobile QA', 'webcasata-elementor-addons' ),
					'item_description' => __( 'Ship reliable mobile releases with real-device coverage, AI-assisted script healing and rapid regression cycles.', 'webcasata-elementor-addons' ),
					'read_more_text'   => __( 'Read more', 'webcasata-elementor-addons' ),
				],
			],
		] );

		$this->add_control( 'active_item', [
			'label'       => __( 'Default Open Item', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => 1,
			'min'         => 1,
			'description' => __( 'Which item number is open (and which image shows) when the page loads. 1 = first item.', 'webcasata-elementor-addons' ),
		] );

		$this->end_controls_section();
	}

	private function register_content_settings_section() {
		$this->start_controls_section( 'section_settings', [
			'label' => __( 'Settings', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'trigger', [
			'label'       => __( 'Open Accordion On', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'click',
			'options'     => [
				'click' => __( 'Click', 'webcasata-elementor-addons' ),
				'hover' => __( 'Hover', 'webcasata-elementor-addons' ),
			],
			'description' => __( 'Hover opens the item (and swaps the image) when the pointer rests on its heading. Touch screens and keyboard users can still tap / press Enter.', 'webcasata-elementor-addons' ),
		] );

		$this->add_control( 'hover_delay', [
			'label'       => __( 'Hover Delay (ms)', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => 120,
			'min'         => 0,
			'max'         => 1000,
			'step'        => 10,
			'description' => __( 'How long the pointer must rest on a heading before it opens. A small delay stops items flickering open as the mouse travels down the list.', 'webcasata-elementor-addons' ),
			'condition'   => [ 'trigger' => 'hover' ],
		] );

		$this->add_control( 'read_more_new_tab', [
			'label'   => __( 'Open Read More Links In New Tab', 'webcasata-elementor-addons' ),
			'separator' => 'before',
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'read_more_nofollow', [
			'label'   => __( 'Add "nofollow" To Read More Links', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'mobile_heading', [
			'label'     => __( 'Mobile Layout', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'mobile_image_position', [
			'label'       => __( 'Image Position', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'before',
			'options'     => [
				'before' => __( 'Before Content', 'webcasata-elementor-addons' ),
				'after'  => __( 'After Content', 'webcasata-elementor-addons' ),
			],
			'description' => __( 'On mobile, the shared side image panel is replaced by each item\'s own image shown inside its own accordion content, either above or below the description.', 'webcasata-elementor-addons' ),
		] );

		$this->end_controls_section();
	}

	/* -----------------------------------------------------------------
	 * STYLE TAB
	 * --------------------------------------------------------------- */

	private function register_style_layout_section() {
		$this->start_controls_section( 'section_style_layout', [
			'label' => __( 'Layout', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'columns_gap', [
			'label'      => __( 'Gap Between Columns', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 48 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-widget' => 'gap: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'image_panel_width', [
			'label'      => __( 'Image Panel Width', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%' ],
			'range'      => [ '%' => [ 'min' => 25, 'max' => 65 ] ],
			'default'    => [ 'unit' => '%', 'size' => 48 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-image-panel' => 'flex-basis: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_title_section() {
		$this->start_controls_section( 'section_style_title', [
			'label' => __( 'Title', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .qyrus-accordion-title-text',
		] );

		$this->start_controls_tabs( 'title_colors_tabs' );

		$this->start_controls_tab( 'title_colors_normal', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control( 'title_color', [
			'label'     => __( 'Text Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#1B1F3B',
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-title-text' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'title_bg_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-title' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_control( 'icon_color', [
			'label'     => __( 'Icon Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#1B1F3B',
			'selectors' => [
				'{{WRAPPER}} .qyrus-accordion-icon::before, {{WRAPPER}} .qyrus-accordion-icon::after' => 'background-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'title_colors_active', [ 'label' => __( 'Active', 'webcasata-elementor-addons' ) ] );
		$this->add_control( 'title_active_color', [
			'label'     => __( 'Text Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-item.active .qyrus-accordion-title-text' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'title_active_bg_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-item.active .qyrus-accordion-title' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_control( 'icon_active_color', [
			'label'     => __( 'Icon Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .qyrus-accordion-item.active .qyrus-accordion-icon::before, {{WRAPPER}} .qyrus-accordion-item.active .qyrus-accordion-icon::after' => 'background-color: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control( 'title_divider_heading', [
			'label'     => __( 'Divider', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_control( 'divider_color', [
			'label'     => __( 'Divider Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#E5E5EA',
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-item' => 'border-color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'title_padding', [
			'label'      => __( 'Padding', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'separator'  => 'before',
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_description_section() {
		$this->start_controls_section( 'section_style_description', [
			'label' => __( 'Description', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'description_typography',
			'selector' => '{{WRAPPER}} .qyrus-accordion-description',
		] );

		$this->add_control( 'description_color', [
			'label'     => __( 'Text Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#55597A',
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-description' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'description_spacing', [
			'label'      => __( 'Spacing After', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 16 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-description' => 'margin-bottom: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_button_section() {
		$this->start_controls_section( 'section_style_button', [
			'label' => __( 'Read More Button', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'button_typography',
			'selector' => '{{WRAPPER}} .qyrus-accordion-readmore',
		] );

		$this->start_controls_tabs( 'button_colors_tabs' );

		$this->start_controls_tab( 'button_colors_normal', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control( 'button_text_color', [
			'label'     => __( 'Text Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#63B31E',
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-readmore' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'button_bg_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-readmore' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'button_colors_hover', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control( 'button_text_hover_color', [
			'label'     => __( 'Text Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-readmore:hover' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'button_bg_hover_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-accordion-readmore:hover' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'button_border',
			'selector' => '{{WRAPPER}} .qyrus-accordion-readmore',
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'button_border_radius', [
			'label'      => __( 'Border Radius', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-readmore' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'button_padding', [
			'label'      => __( 'Padding', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-readmore' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_image_section() {
		$this->start_controls_section( 'section_style_image', [
			'label' => __( 'Image Panel', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'panel_style_note', [
			'raw'             => __( 'These controls style both the desktop side image panel and the inline image shown inside each item on mobile.', 'webcasata-elementor-addons' ),
			'type'            => Controls_Manager::RAW_HTML,
			'content_classes' => 'elementor-descriptor',
		] );

		$this->add_control( 'panel_bg_color', [
			'label'     => __( 'Panel Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#F5F7FA',
			'selectors' => [
				'{{WRAPPER}} .qyrus-accordion-image-panel, {{WRAPPER}} .qyrus-accordion-mobile-image-wrap' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'panel_border',
			'selector' => '{{WRAPPER}} .qyrus-accordion-image-panel, {{WRAPPER}} .qyrus-accordion-mobile-image-wrap',
		] );

		$this->add_responsive_control( 'panel_border_radius', [
			'label'      => __( 'Panel Border Radius', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default'    => [ 'top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-image-panel, {{WRAPPER}} .qyrus-accordion-mobile-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'panel_padding', [
			'label'      => __( 'Panel Padding', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'default'    => [ 'top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-image-panel, {{WRAPPER}} .qyrus-accordion-mobile-image-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'panel_box_shadow',
			'selector' => '{{WRAPPER}} .qyrus-accordion-image-panel, {{WRAPPER}} .qyrus-accordion-mobile-image-wrap',
		] );

		$this->add_responsive_control( 'mobile_image_spacing', [
			'label'      => __( 'Mobile Image Spacing', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 16 ],
			'description' => __( 'Space above and below the inline image on mobile.', 'webcasata-elementor-addons' ),
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-mobile-image-wrap' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'image_heading', [
			'label'     => __( 'Image', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		$this->add_responsive_control( 'image_border_radius', [
			'label'      => __( 'Image Border Radius', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default'    => [ 'top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-accordion-image, {{WRAPPER}} .qyrus-accordion-mobile-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'image_animation_heading', [
			'label'     => __( 'Image Switch Animation', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		] );

		// prefix_class puts "qyrus-img-anim-{value}" on the widget's outer
		// wrapper, so the CSS picks the keyframes with no PHP re-render.
		$this->add_control( 'image_animation', [
			'label'        => __( 'Animation', 'webcasata-elementor-addons' ),
			'type'         => Controls_Manager::SELECT,
			'default'      => 'fade',
			'prefix_class' => 'qyrus-img-anim-',
			'options'      => [
				'none'             => __( 'None', 'webcasata-elementor-addons' ),
				'fade'             => __( 'Fade', 'webcasata-elementor-addons' ),
				'slide-from-bottom' => __( 'Slide In From Bottom', 'webcasata-elementor-addons' ),
				'slide-from-top'   => __( 'Slide In From Top', 'webcasata-elementor-addons' ),
				'slide-from-left'  => __( 'Slide In From Left', 'webcasata-elementor-addons' ),
				'slide-from-right' => __( 'Slide In From Right', 'webcasata-elementor-addons' ),
				'zoom-in'          => __( 'Zoom In', 'webcasata-elementor-addons' ),
				'zoom-out'         => __( 'Zoom Out', 'webcasata-elementor-addons' ),
				'blur'             => __( 'Blur In', 'webcasata-elementor-addons' ),
			],
			'description'  => __( 'Plays on the new image each time the active accordion item changes (desktop/tablet side panel).', 'webcasata-elementor-addons' ),
		] );

		$this->add_control( 'image_transition_duration', [
			'label'       => __( 'Animation Duration (ms)', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => 400,
			'min'         => 50,
			'max'         => 2000,
			'step'        => 50,
			'condition'   => [ 'image_animation!' => 'none' ],
			'selectors'   => [
				'{{WRAPPER}} .qyrus-accordion-image' => 'animation-duration: {{VALUE}}ms;',
			],
		] );

		$this->add_control( 'image_animation_easing', [
			'label'     => __( 'Easing', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => 'ease',
			'options'   => [
				'ease'                         => __( 'Ease', 'webcasata-elementor-addons' ),
				'ease-out'                     => __( 'Ease Out', 'webcasata-elementor-addons' ),
				'ease-in-out'                  => __( 'Ease In Out', 'webcasata-elementor-addons' ),
				'linear'                       => __( 'Linear', 'webcasata-elementor-addons' ),
				'cubic-bezier(0.22, 1, 0.36, 1)' => __( 'Smooth (Gentle Stop)', 'webcasata-elementor-addons' ),
			],
			'condition' => [ 'image_animation!' => 'none' ],
			'selectors' => [
				'{{WRAPPER}} .qyrus-accordion-image' => 'animation-timing-function: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	/* -----------------------------------------------------------------
	 * RENDER
	 * --------------------------------------------------------------- */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items    = $settings['items'];

		if ( empty( $items ) ) {
			return;
		}

		$active_index = max( 1, (int) $settings['active_item'] ) - 1;
		if ( $active_index > count( $items ) - 1 ) {
			$active_index = 0;
		}

		$trigger = ( 'hover' === $settings['trigger'] ) ? 'hover' : 'click';

		$this->add_render_attribute( 'wrapper', [
			'class'            => 'qyrus-accordion-widget',
			'data-trigger'     => $trigger,
			'data-hover-delay' => ( 'hover' === $trigger ) ? absint( $settings['hover_delay'] ) : 0,
		] );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<div class="qyrus-accordion-list" role="tablist">
				<?php foreach ( $items as $index => $item ) :
					$is_active = ( (int) $index === $active_index );
					$image_url = ! empty( $item['item_image']['url'] ) ? $item['item_image']['url'] : '';
					$link_key  = 'read_more_link_' . $index;
					$has_link  = ! empty( $item['read_more_link']['url'] );

					if ( $has_link ) {
						$this->add_link_attributes( $link_key, $item['read_more_link'] );
						if ( 'yes' === $settings['read_more_new_tab'] ) {
							$this->add_render_attribute( $link_key, 'target', '_blank' );
						}
						if ( 'yes' === $settings['read_more_nofollow'] ) {
							$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
						}
					}
					?>
					<div class="qyrus-accordion-item<?php echo $is_active ? ' active' : ''; ?>"
						 data-index="<?php echo esc_attr( $index ); ?>"
						 data-image="<?php echo esc_url( $image_url ); ?>">

						<div class="qyrus-accordion-title" role="button" tabindex="0"
							 aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">
							<span class="qyrus-accordion-title-text"><?php echo wp_kses_post( $item['item_title'] ); ?></span>
							<span class="qyrus-accordion-icon" aria-hidden="true"></span>
						</div>

						<div class="qyrus-accordion-content" <?php echo ! $is_active ? 'style="display:none;"' : ''; ?>>
							<?php
							$mobile_image = '';
							if ( ! empty( $image_url ) ) {
								$mobile_image = sprintf(
									'<div class="qyrus-accordion-mobile-image-wrap"><img src="%1$s" alt="%2$s" class="qyrus-accordion-mobile-image" /></div>',
									esc_url( $image_url ),
									esc_attr( wp_strip_all_tags( $item['item_title'] ) )
								);
							}

							if ( $mobile_image && 'before' === $settings['mobile_image_position'] ) {
								echo $mobile_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
							}
							?>

							<?php if ( ! empty( $item['item_description'] ) ) : ?>
								<div class="qyrus-accordion-description">
									<?php echo wp_kses_post( wpautop( $item['item_description'] ) ); ?>
								</div>
							<?php endif; ?>

							<?php if ( $has_link && ! empty( $item['read_more_text'] ) ) : ?>
								<a <?php echo $this->get_render_attribute_string( $link_key ); ?> class="qyrus-accordion-readmore">
									<span><?php echo esc_html( $item['read_more_text'] ); ?></span>
									<span class="qyrus-readmore-arrow" aria-hidden="true">&rarr;</span>
								</a>
							<?php endif; ?>

							<?php
							if ( $mobile_image && 'after' === $settings['mobile_image_position'] ) {
								echo $mobile_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
							}
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="qyrus-accordion-image-panel">
				<?php foreach ( $items as $index => $item ) :
					$is_active = ( (int) $index === $active_index );
					$image_url = ! empty( $item['item_image']['url'] ) ? $item['item_image']['url'] : '';
					if ( empty( $image_url ) ) {
						continue;
					}
					?>
					<img src="<?php echo esc_url( $image_url ); ?>"
						 alt="<?php echo esc_attr( wp_strip_all_tags( $item['item_title'] ) ); ?>"
						 data-index="<?php echo esc_attr( $index ); ?>"
						 class="qyrus-accordion-image<?php echo $is_active ? ' active' : ''; ?>" />
				<?php endforeach; ?>
			</div>

		</div>
		<?php
	}
}
