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
use Elementor\Icons_Manager;

class Qyrus_Feature_Grid_Widget extends Widget_Base {

	public function get_name() {
		return 'qyrus-feature-grid';
	}

	public function get_title() {
		return __( 'Webcasata Feature Grid', 'webcasata-elementor-addons' );
	}

	public function get_icon() {
		return 'eicon-icon-box';
	}

	public function get_categories() {
		return [ 'webcasata' ];
	}

	public function get_keywords() {
		return [ 'feature', 'grid', 'icon', 'box', 'cards', 'webcasata' ];
	}

	public function get_style_depends() {
		return [ 'qyrus-feature-grid' ];
	}

	protected function register_controls() {
		$this->register_content_items_section();
		$this->register_content_layout_section();
		$this->register_style_card_section();
		$this->register_style_icon_section();
		$this->register_style_title_section();
		$this->register_style_description_section();
	}

	/* -----------------------------------------------------------------
	 * CONTENT TAB
	 * --------------------------------------------------------------- */

	private function register_content_items_section() {
		$this->start_controls_section( 'section_items', [
			'label' => __( 'Features', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'feature_icon', [
			'label'   => __( 'Icon', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::ICONS,
			'default' => [
				'value'   => 'fas fa-bolt',
				'library' => 'fa-solid',
			],
		] );

		$repeater->add_control( 'feature_title', [
			'label'       => __( 'Title', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Feature Title', 'webcasata-elementor-addons' ),
			'label_block' => true,
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'feature_description', [
			'label'       => __( 'Description', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXTAREA,
			'default'     => __( 'A short description of this feature goes here.', 'webcasata-elementor-addons' ),
			'label_block' => true,
			'rows'        => 4,
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'feature_link', [
			'label'         => __( 'Link (Optional)', 'webcasata-elementor-addons' ),
			'type'          => Controls_Manager::URL,
			'placeholder'   => __( 'https://your-link.com', 'webcasata-elementor-addons' ),
			'show_external' => true,
			'default'       => [
				'url'         => '',
				'is_external' => false,
				'nofollow'    => false,
			],
			'description'   => __( 'If set, the whole card becomes clickable.', 'webcasata-elementor-addons' ),
			'dynamic'       => [ 'active' => true ],
		] );

		$this->add_control( 'features', [
			'label'       => __( 'Items', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ feature_title }}}',
			'default'     => [
				[
					'feature_icon'        => [ 'value' => 'fas fa-diagram-project', 'library' => 'fa-solid' ],
					'feature_title'       => __( 'Agentic SEER Framework', 'webcasata-elementor-addons' ),
					'feature_description' => __( 'An autonomous AI-driven test orchestration framework that senses, evaluates, executes and reports.', 'webcasata-elementor-addons' ),
				],
				[
					'feature_icon'        => [ 'value' => 'fas fa-globe', 'library' => 'fa-solid' ],
					'feature_title'       => __( 'Web App Testing', 'webcasata-elementor-addons' ),
					'feature_description' => __( 'Low-code/no-code testing with an AI-powered recorder and automated script healing.', 'webcasata-elementor-addons' ),
				],
				[
					'feature_icon'        => [ 'value' => 'fas fa-mobile-screen', 'library' => 'fa-solid' ],
					'feature_title'       => __( 'Mobile QA', 'webcasata-elementor-addons' ),
					'feature_description' => __( 'Real-device coverage with rapid regression cycles for every release.', 'webcasata-elementor-addons' ),
				],
				[
					'feature_icon'        => [ 'value' => 'fas fa-plug', 'library' => 'fa-solid' ],
					'feature_title'       => __( 'API Testing', 'webcasata-elementor-addons' ),
					'feature_description' => __( 'Master your full API lifecycle from contract validation to load testing.', 'webcasata-elementor-addons' ),
				],
			],
		] );

		$this->end_controls_section();
	}

	private function register_content_layout_section() {
		$this->start_controls_section( 'section_layout', [
			'label' => __( 'Layout', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_responsive_control( 'columns', [
			'label'          => __( 'Columns', 'webcasata-elementor-addons' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'options'        => [
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			],
			'selectors'      => [
				'{{WRAPPER}} .qyrus-feature-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
			],
		] );

		$this->add_responsive_control( 'grid_gap', [
			'label'      => __( 'Gap', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 24 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-grid' => 'gap: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'card_alignment', [
			'label'   => __( 'Card Content Alignment', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'left',
			'options' => [
				'left'   => __( 'Left', 'webcasata-elementor-addons' ),
				'center' => __( 'Center', 'webcasata-elementor-addons' ),
			],
		] );

		$this->add_control( 'title_html_tag', [
			'label'   => __( 'Title HTML Tag', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h3',
			'options' => [
				'h2'   => 'H2',
				'h3'   => 'H3',
				'h4'   => 'H4',
				'h5'   => 'H5',
				'h6'   => 'H6',
				'div'  => 'div',
				'span' => 'span',
			],
		] );

		$this->end_controls_section();
	}

	/* -----------------------------------------------------------------
	 * STYLE TAB
	 * --------------------------------------------------------------- */

	private function register_style_card_section() {
		$this->start_controls_section( 'section_style_card', [
			'label' => __( 'Card', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->start_controls_tabs( 'card_tabs' );

		$this->start_controls_tab( 'card_tab_normal', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control( 'card_bg_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#FFFFFF',
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-card' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'card_border',
			'selector' => '{{WRAPPER}} .qyrus-feature-card',
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'card_box_shadow',
			'selector' => '{{WRAPPER}} .qyrus-feature-card',
		] );
		$this->end_controls_tab();

		$this->start_controls_tab( 'card_tab_hover', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control( 'card_bg_hover_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-card:hover' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_control( 'card_border_hover_color', [
			'label'     => __( 'Border Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'condition' => [ 'card_border_border!' => '' ],
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-card:hover' => 'border-color: {{VALUE}};' ],
		] );
		$this->add_control( 'card_hover_lift', [
			'label'        => __( 'Lift On Hover', 'webcasata-elementor-addons' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
			'selectors_dictionary' => [
				'yes' => 'translateY(-4px)',
			],
			'selectors'    => [
				'{{WRAPPER}} .qyrus-feature-card:hover' => 'transform: {{VALUE}};',
			],
		] );
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control( 'card_padding', [
			'label'      => __( 'Padding', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'default'    => [ 'top' => 32, 'right' => 28, 'bottom' => 32, 'left' => 28, 'unit' => 'px' ],
			'separator'  => 'before',
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'card_radius', [
			'label'      => __( 'Border Radius', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default'    => [ 'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_icon_section() {
		$this->start_controls_section( 'section_style_icon', [
			'label' => __( 'Icon', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'icon_size', [
			'label'      => __( 'Icon Size', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 12, 'max' => 60 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 24 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-icon' => 'font-size: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'icon_box_size', [
			'label'      => __( 'Icon Box Size', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 24, 'max' => 120 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 56 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'icon_color', [
			'label'     => __( 'Icon Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#63B31E',
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-icon' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'icon_bg_color', [
			'label'     => __( 'Icon Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#EEF7E3',
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-icon' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_control( 'icon_shape', [
			'label'   => __( 'Icon Shape', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'circle',
			'options' => [
				'circle' => __( 'Circle', 'webcasata-elementor-addons' ),
				'square' => __( 'Rounded Square', 'webcasata-elementor-addons' ),
				'none'   => __( 'None', 'webcasata-elementor-addons' ),
			],
			'selectors_dictionary' => [
				'circle' => '999px',
				'square' => '12px',
				'none'   => '0px',
			],
			'selectors' => [
				'{{WRAPPER}} .qyrus-feature-icon' => 'border-radius: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'icon_spacing', [
			'label'      => __( 'Spacing After Icon', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 20 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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
			'selector' => '{{WRAPPER}} .qyrus-feature-title',
		] );

		$this->add_control( 'title_color', [
			'label'     => __( 'Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#1B1F3B',
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_control( 'title_hover_color', [
			'label'     => __( 'Hover Color (Linked Cards)', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} a.qyrus-feature-card:hover .qyrus-feature-title' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'title_spacing', [
			'label'      => __( 'Spacing After', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 10 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-feature-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
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
			'selector' => '{{WRAPPER}} .qyrus-feature-description',
		] );

		$this->add_control( 'description_color', [
			'label'     => __( 'Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#55597A',
			'selectors' => [ '{{WRAPPER}} .qyrus-feature-description' => 'color: {{VALUE}};' ],
		] );

		$this->end_controls_section();
	}

	/* -----------------------------------------------------------------
	 * RENDER
	 * --------------------------------------------------------------- */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$features = $settings['features'];

		if ( empty( $features ) ) {
			return;
		}

		$title_tag = in_array( $settings['title_html_tag'], [ 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span' ], true )
			? $settings['title_html_tag']
			: 'h3';

		$this->add_render_attribute( 'wrapper', 'class', [
			'qyrus-feature-grid',
			'qyrus-align-' . ( 'center' === $settings['card_alignment'] ? 'center' : 'left' ),
		] );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes every attribute value. ?>>
			<?php foreach ( $features as $index => $feature ) :
				$card_key = 'card_' . $index;
				$has_link = ! empty( $feature['feature_link']['url'] );
				$tag      = $has_link ? 'a' : 'div';

				$this->add_render_attribute( $card_key, 'class', 'qyrus-feature-card' );

				if ( $has_link ) {
					$this->add_link_attributes( $card_key, $feature['feature_link'] );
				}
				?>
				<<?php echo esc_attr( $tag ); ?> <?php echo $this->get_render_attribute_string( $card_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes every attribute value. ?>>
					<?php if ( ! empty( $feature['feature_icon']['value'] ) ) : ?>
						<span class="qyrus-feature-icon">
							<?php Icons_Manager::render_icon( $feature['feature_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $feature['feature_title'] ) ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="qyrus-feature-title">
							<?php echo wp_kses_post( $feature['feature_title'] ); ?>
						</<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $feature['feature_description'] ) ) : ?>
						<div class="qyrus-feature-description">
							<?php echo wp_kses_post( wpautop( $feature['feature_description'] ) ); ?>
						</div>
					<?php endif; ?>
				</<?php echo esc_attr( $tag ); ?>>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
