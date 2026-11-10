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

class Qyrus_Icon_Marquee_Widget extends Widget_Base {

	public function get_name() {
		return 'qyrus-icon-marquee';
	}

	public function get_title() {
		return __( 'Webcasata Icon Scroll', 'webcasata-elementor-addons' );
	}

	public function get_icon() {
		return 'eicon-slider-3d';
	}

	public function get_categories() {
		return [ 'webcasata' ];
	}

	public function get_keywords() {
		return [ 'marquee', 'slider', 'logo', 'icon', 'carousel', 'integrations', 'webcasata', 'scroll' ];
	}

	public function get_style_depends() {
		return [ 'qyrus-icon-marquee' ];
	}

	protected function register_controls() {
		$this->register_content_icons_section();
		$this->register_content_settings_section();
		$this->register_style_layout_section();
		$this->register_style_icon_box_section();
		$this->register_style_caption_section();
	}

	/* -----------------------------------------------------------------
	 * CONTENT TAB
	 * --------------------------------------------------------------- */

	private function register_content_icons_section() {
		$this->start_controls_section( 'section_icons', [
			'label' => __( 'Icons', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'icon_image', [
			'label'   => __( 'Icon', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::MEDIA,
			'dynamic' => [ 'active' => true ],
			'default' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		] );

		$repeater->add_control( 'icon_name', [
			'label'       => __( 'Icon Name', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Integration', 'webcasata-elementor-addons' ),
			'label_block' => true,
			'description' => __( 'Used as the image alt text, and optionally shown as a caption.', 'webcasata-elementor-addons' ),
			'dynamic'     => [ 'active' => true ],
		] );

		$repeater->add_control( 'icon_link', [
			'label'         => __( 'Icon Link', 'webcasata-elementor-addons' ),
			'type'          => Controls_Manager::URL,
			'placeholder'   => __( 'https://your-link.com', 'webcasata-elementor-addons' ),
			'show_external' => true,
			'default'       => [
				'url'         => '',
				'is_external' => false,
				'nofollow'    => false,
			],
			'dynamic'       => [ 'active' => true ],
		] );

		$this->add_control( 'icons', [
			'label'       => __( 'Items', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ icon_name }}}',
			'default'     => [
				[ 'icon_name' => __( 'Integration 1', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 2', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 3', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 4', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 5', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 6', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 7', 'webcasata-elementor-addons' ) ],
				[ 'icon_name' => __( 'Integration 8', 'webcasata-elementor-addons' ) ],
			],
		] );

		$this->end_controls_section();
	}

	private function register_content_settings_section() {
		$this->start_controls_section( 'section_settings', [
			'label' => __( 'Settings', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'direction', [
			'label'   => __( 'Slide Direction', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'vertical',
			'options' => [
				'vertical'   => __( 'Vertical', 'webcasata-elementor-addons' ),
				'horizontal' => __( 'Horizontal', 'webcasata-elementor-addons' ),
			],
		] );

		$this->add_control( 'lanes', [
			'label'     => __( 'Columns', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => 4,
			'min'       => 1,
			'max'       => 8,
			'condition' => [ 'direction' => 'vertical' ],
		] );

		$this->add_control( 'rows', [
			'label'     => __( 'Rows', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::NUMBER,
			'default'   => 1,
			'min'       => 1,
			'max'       => 6,
			'condition' => [ 'direction' => 'horizontal' ],
		] );

		$this->add_control( 'marquee_height', [
			'label'     => __( 'Marquee Height', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'     => [ 'px' => [ 'min' => 150, 'max' => 800 ] ],
			'default'   => [ 'unit' => 'px', 'size' => 380 ],
			'condition' => [ 'direction' => 'vertical' ],
			'selectors' => [
				'{{WRAPPER}} .qyrus-marquee-vertical .qyrus-marquee-lanes' => 'height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'speed', [
			'label'       => __( 'Speed (Seconds Per Loop)', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::NUMBER,
			'default'     => 30,
			'min'         => 5,
			'max'         => 120,
			'description' => __( 'Lower is faster.', 'webcasata-elementor-addons' ),
		] );

		$this->add_control( 'alternate_direction', [
			'label'   => __( 'Alternate Column/Row Direction', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'reverse_all', [
			'label'   => __( 'Reverse Direction', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'pause_on_hover', [
			'label'   => __( 'Pause On Hover', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'show_icon_name', [
			'label'   => __( 'Show Icon Name', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'new_tab', [
			'label'   => __( 'Open Links In New Tab', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->add_control( 'nofollow', [
			'label'   => __( 'Add "nofollow" To Links', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
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

		$this->add_responsive_control( 'lane_gap', [
			'label'      => __( 'Gap Between Columns/Rows', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 20 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-marquee-lanes' => 'gap: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'icon_gap', [
			'label'       => __( 'Gap Between Icons', 'webcasata-elementor-addons' ),
			'type'        => Controls_Manager::SLIDER,
			'size_units'  => [ 'px' ],
			'range'       => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'default'     => [ 'unit' => 'px', 'size' => 20 ],
			'description' => __( 'Applied as spacing on each icon rather than a flex gap, so the infinite scroll loop stays perfectly seamless.', 'webcasata-elementor-addons' ),
			'selectors'   => [
				'{{WRAPPER}} .qyrus-marquee-vertical .qyrus-marquee-item'   => 'margin-bottom: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .qyrus-marquee-horizontal .qyrus-marquee-item' => 'margin-right: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_icon_box_section() {
		$this->start_controls_section( 'section_style_icon_box', [
			'label' => __( 'Icon Box', 'webcasata-elementor-addons' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'logo_width', [
			'label'      => __( 'Logo Width', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 32, 'max' => 240 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 96 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-marquee-icon-box' => 'width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'logo_height', [
			'label'      => __( 'Logo Height', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 32, 'max' => 240 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 96 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-marquee-icon-box' => 'height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'icon_padding', [
			'label'      => __( 'Icon Padding', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'default'    => [ 'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-marquee-icon-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'icon_bg_color', [
			'label'     => __( 'Background Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#FFFFFF',
			'selectors' => [ '{{WRAPPER}} .qyrus-marquee-icon-box' => 'background-color: {{VALUE}};' ],
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'icon_border',
			'selector' => '{{WRAPPER}} .qyrus-marquee-icon-box',
		] );

		$this->add_responsive_control( 'icon_border_radius', [
			'label'      => __( 'Border Radius', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default'    => [ 'top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-marquee-icon-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'icon_box_shadow',
			'selector' => '{{WRAPPER}} .qyrus-marquee-icon-box',
		] );

		$this->add_control( 'grayscale_effect', [
			'label'   => __( 'Grayscale Until Hover', 'webcasata-elementor-addons' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => '',
		] );

		$this->end_controls_section();
	}

	private function register_style_caption_section() {
		$this->start_controls_section( 'section_style_caption', [
			'label'     => __( 'Caption', 'webcasata-elementor-addons' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_icon_name' => 'yes' ],
		] );

		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'caption_typography',
			'selector' => '{{WRAPPER}} .qyrus-marquee-caption',
		] );

		$this->add_control( 'caption_color', [
			'label'     => __( 'Color', 'webcasata-elementor-addons' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#55597A',
			'selectors' => [ '{{WRAPPER}} .qyrus-marquee-caption' => 'color: {{VALUE}};' ],
		] );

		$this->add_responsive_control( 'caption_spacing', [
			'label'      => __( 'Spacing Above', 'webcasata-elementor-addons' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
			'default'    => [ 'unit' => 'px', 'size' => 8 ],
			'selectors'  => [
				'{{WRAPPER}} .qyrus-marquee-caption' => 'margin-top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	/* -----------------------------------------------------------------
	 * RENDER
	 * --------------------------------------------------------------- */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$icons    = $settings['icons'];

		if ( empty( $icons ) ) {
			return;
		}

		$direction  = 'horizontal' === $settings['direction'] ? 'horizontal' : 'vertical';
		$lane_count = 'vertical' === $direction
			? max( 1, (int) $settings['lanes'] )
			: max( 1, (int) $settings['rows'] );

		$lanes = array_fill( 0, $lane_count, [] );
		foreach ( $icons as $i => $icon ) {
			if ( empty( $icon['icon_image']['url'] ) ) {
				continue;
			}
			$lanes[ $i % $lane_count ][] = $icon;
		}

		$wrapper_classes = [ 'qyrus-icon-marquee-widget', 'qyrus-marquee-' . $direction ];
		if ( 'yes' === $settings['pause_on_hover'] ) {
			$wrapper_classes[] = 'qyrus-pause-on-hover';
		}
		if ( 'yes' === $settings['grayscale_effect'] ) {
			$wrapper_classes[] = 'qyrus-marquee-widget-grayscale';
		}

		$this->add_render_attribute( 'wrapper', 'class', $wrapper_classes );
		$this->add_render_attribute( 'wrapper', 'style', '--qyrus-marquee-duration: ' . max( 1, absint( $settings['speed'] ) ) . 's;' );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes every attribute value. ?>>
			<div class="qyrus-marquee-lanes">
				<?php foreach ( $lanes as $lane_index => $lane_icons ) :
					if ( empty( $lane_icons ) ) {
						continue;
					}

					$is_odd_lane = (bool) ( $lane_index % 2 );
					$reversed    = ( 'yes' === $settings['alternate_direction'] ) ? $is_odd_lane : false;
					if ( 'yes' === $settings['reverse_all'] ) {
						$reversed = ! $reversed;
					}
					?>
					<div class="qyrus-marquee-lane<?php echo $reversed ? ' qyrus-marquee-reverse' : ''; ?>">
						<div class="qyrus-marquee-track">
							<?php
							for ( $copy = 0; $copy < 2; $copy++ ) :
								foreach ( $lane_icons as $item_index => $icon ) :
									$item_key = 'icon_' . $lane_index . '_' . $copy . '_' . $item_index;
									$has_link = ! empty( $icon['icon_link']['url'] );
									$tag      = $has_link ? 'a' : 'span';

									$this->add_render_attribute( $item_key, 'class', 'qyrus-marquee-item' );

									if ( $has_link ) {
										$this->add_link_attributes( $item_key, $icon['icon_link'] );
										if ( 'yes' === $settings['new_tab'] ) {
											$this->add_render_attribute( $item_key, 'target', '_blank' );
										}
										if ( 'yes' === $settings['nofollow'] ) {
											$this->add_render_attribute( $item_key, 'rel', 'nofollow' );
										}
									}

									if ( $copy > 0 ) {
										$this->add_render_attribute( $item_key, [
											'aria-hidden' => 'true',
											'tabindex'    => '-1',
										] );
									}
									?>
									<<?php echo esc_attr( $tag ); ?> <?php echo $this->get_render_attribute_string( $item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes every attribute value. ?>>
										<span class="qyrus-marquee-icon-box">
											<img src="<?php echo esc_url( $icon['icon_image']['url'] ); ?>"
												 alt="<?php echo esc_attr( $icon['icon_name'] ); ?>"
												 loading="lazy" />
										</span>
										<?php if ( 'yes' === $settings['show_icon_name'] && ! empty( $icon['icon_name'] ) ) : ?>
											<span class="qyrus-marquee-caption"><?php echo esc_html( $icon['icon_name'] ); ?></span>
										<?php endif; ?>
									</<?php echo esc_attr( $tag ); ?>>
								<?php endforeach;
							endfor;
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
