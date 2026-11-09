<?php
/**
 * Webcasata Video
 *
 * YouTube / Vimeo / self-hosted video that is NOT in the page until it is needed:
 *   - the page only contains a poster image and a play button (no iframe, no
 *     <video>), so nothing is downloaded and no third-party script runs;
 *   - Popup style: the video is created when the visitor clicks, inside a modal,
 *     and removed again when the modal closes;
 *   - Inline style: the video replaces the poster when the visitor clicks, or,
 *     with Auto Play on, when the widget scrolls into view.
 *
 * The embed URL is built here (and only ever for a validated video id); the
 * script (assets/js/webcasata-video.js) turns it into an iframe / <video> on demand.
 *
 * Note for panel texts: Elementor compiles them as Underscore templates, so they
 * must never contain curly-brace pairs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

class Webcasata_Video_Widget extends Widget_Base {

	public function get_name() {
		return 'webcasata-video';
	}

	public function get_title() {
		return __( 'Webcasata Video', 'webcasata-elementor-addons' );
	}

	public function get_icon() {
		return 'eicon-youtube';
	}

	public function get_categories() {
		return [ 'webcasata' ];
	}

	public function get_keywords() {
		return [ 'video', 'youtube', 'vimeo', 'popup', 'lightbox', 'player', 'lazy', 'webcasata' ];
	}

	public function get_style_depends() {
		return [ 'webcasata-video' ];
	}

	public function get_script_depends() {
		return [ 'webcasata-video' ];
	}

	/* ==================================================================
	 * Link parsing and embed URLs (pure functions, unit-tested)
	 * ================================================================== */

	/** YouTube video id from any common URL form, or a bare 11-character id. */
	public static function parse_youtube_id( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $url ) ) {
			return $url;
		}
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . $url;
		}
		$parts = wp_parse_url( $url );
		if ( empty( $parts['host'] ) ) {
			return '';
		}
		$host = strtolower( preg_replace( '/^(www|m|music)\./i', '', $parts['host'] ) );
		$path = isset( $parts['path'] ) ? $parts['path'] : '';
		$id   = '';

		if ( 'youtu.be' === $host ) {
			$segments = explode( '/', trim( $path, '/' ) );
			$id       = $segments[0];
		} elseif ( 'youtube.com' === $host || 'youtube-nocookie.com' === $host ) {
			if ( '/watch' === rtrim( $path, '/' ) && ! empty( $parts['query'] ) ) {
				parse_str( $parts['query'], $q );
				$id = isset( $q['v'] ) && is_string( $q['v'] ) ? $q['v'] : '';
			} elseif ( preg_match( '#^/(?:embed|shorts|live|v)/([^/?]+)#', $path, $m ) ) {
				$id = $m[1];
			}
		}

		return preg_match( '/^[A-Za-z0-9_-]{11}$/', $id ) ? $id : '';
	}

	/** [ id, hash ] for a Vimeo link ( hash is only present on unlisted videos ), or [ '', '' ]. */
	public static function parse_vimeo( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return [ '', '' ];
		}
		if ( preg_match( '/^\d{6,12}$/', $url ) ) {
			return [ $url, '' ];
		}
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . $url;
		}
		$parts = wp_parse_url( $url );
		if ( empty( $parts['host'] ) ) {
			return [ '', '' ];
		}
		$host = strtolower( preg_replace( '/^www\./i', '', $parts['host'] ) );
		if ( 'vimeo.com' !== $host && 'player.vimeo.com' !== $host ) {
			return [ '', '' ];
		}
		$segments = array_values( array_filter( explode( '/', isset( $parts['path'] ) ? $parts['path'] : '' ), 'strlen' ) );
		$id       = '';
		$hash     = '';
		foreach ( $segments as $i => $seg ) {
			if ( preg_match( '/^\d{6,12}$/', $seg ) ) {
				$id = $seg;
				if ( isset( $segments[ $i + 1 ] ) && preg_match( '/^[A-Za-z0-9]{8,16}$/', $segments[ $i + 1 ] ) ) {
					$hash = $segments[ $i + 1 ];
				}
				break;
			}
		}
		if ( '' === $hash && ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $q );
			if ( isset( $q['h'] ) && is_string( $q['h'] ) && preg_match( '/^[A-Za-z0-9]{8,16}$/', $q['h'] ) ) {
				$hash = $q['h'];
			}
		}

		return '' === $id ? [ '', '' ] : [ $id, $hash ];
	}

	/**
	 * Embed URL WITHOUT autoplay (the script adds autoplay=1 at the moment it plays).
	 *
	 * @param string $type  youtube|vimeo
	 * @param array  $o     id, hash, mute, loop, controls, privacy, start, end
	 */
	public static function embed_url( $type, array $o ) {
		$start = isset( $o['start'] ) ? max( 0, (int) $o['start'] ) : 0;
		$end   = isset( $o['end'] ) ? max( 0, (int) $o['end'] ) : 0;

		if ( 'youtube' === $type ) {
			$q = [
				'rel'      => 0,
				'playsinline' => 1,
				'controls' => ! empty( $o['controls'] ) ? 1 : 0,
			];
			if ( ! empty( $o['mute'] ) ) {
				$q['mute'] = 1;
			}
			if ( ! empty( $o['loop'] ) ) {
				$q['loop']     = 1;
				$q['playlist'] = $o['id']; // YouTube only loops a single video when playlist = its own id.
			}
			if ( $start ) {
				$q['start'] = $start;
			}
			if ( $end ) {
				$q['end'] = $end;
			}
			$host = ! empty( $o['privacy'] ) ? 'www.youtube-nocookie.com' : 'www.youtube.com';

			return 'https://' . $host . '/embed/' . rawurlencode( $o['id'] ) . '?' . http_build_query( $q, '', '&' );
		}

		// Vimeo.
		$q = [
			'playsinline' => 1,
			'controls'    => ! empty( $o['controls'] ) ? 1 : 0,
			'title'       => 0,
			'byline'      => 0,
			'portrait'    => 0,
		];
		if ( ! empty( $o['hash'] ) ) {
			$q['h'] = $o['hash'];
		}
		if ( ! empty( $o['mute'] ) ) {
			$q['muted'] = 1;
		}
		if ( ! empty( $o['loop'] ) ) {
			$q['loop'] = 1;
		}
		if ( ! empty( $o['privacy'] ) ) {
			$q['dnt'] = 1;
		}
		$url = 'https://player.vimeo.com/video/' . rawurlencode( $o['id'] ) . '?' . http_build_query( $q, '', '&' );

		return $start ? $url . '#t=' . $start . 's' : $url;
	}

	/** Self-hosted file with a media-fragment start / end. */
	public static function hosted_url( $url, $start, $end ) {
		$start = max( 0, (int) $start );
		$end   = max( 0, (int) $end );
		if ( ! $start && ! $end ) {
			return $url;
		}

		return $url . '#t=' . $start . ( $end ? ',' . $end : '' );
	}

	/** Vimeo thumbnail through oEmbed, cached (also caches a failure briefly so a slow API never slows the page). */
	private function vimeo_thumbnail( $id, $hash ) {
		$key    = 'wv_vimeo_' . md5( $id . $hash );
		$cached = get_transient( $key );
		if ( false !== $cached ) {
			return (string) $cached;
		}

		$video_url = 'https://vimeo.com/' . $id . ( $hash ? '/' . $hash : '' );
		$response  = wp_remote_get(
			add_query_arg(
				[
					'url'   => rawurlencode( $video_url ),
					'width' => 1280,
				],
				'https://vimeo.com/api/oembed.json'
			),
			[ 'timeout' => 3 ]
		);

		$thumb = '';
		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( is_array( $data ) && ! empty( $data['thumbnail_url'] ) ) {
				$thumb = esc_url_raw( $data['thumbnail_url'] );
			}
		}
		set_transient( $key, $thumb, '' === $thumb ? HOUR_IN_SECONDS : WEEK_IN_SECONDS );

		return $thumb;
	}

	/* ==================================================================
	 * Controls
	 * ================================================================== */

	private function W( $suffix = '' ) {
		return '{{WRAPPER}}' . $suffix;
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_poster_controls();

		$this->register_style_frame();
		$this->register_style_overlay();
		$this->register_style_button();
		$this->register_style_glow();
		$this->register_style_popup();
	}

	private function register_content_controls() {
		$this->start_controls_section(
			'section_video',
			[
				'label' => __( 'Video', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'video_type',
			[
				'label'   => __( 'Video Type', 'webcasata-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube' => __( 'Youtube', 'webcasata-elementor-addons' ),
					'vimeo'   => __( 'Vimeo', 'webcasata-elementor-addons' ),
					'hosted'  => __( 'Self Hosted', 'webcasata-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => __( 'Link', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'placeholder' => __( 'Paste the video link', 'webcasata-elementor-addons' ),
				'label_block' => true,
				'condition'   => [ 'video_type' => [ 'youtube', 'vimeo' ] ],
			]
		);

		$this->add_control(
			'hosted_source',
			[
				'label'     => __( 'Source', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'library',
				'options'   => [
					'library'  => __( 'Media Library', 'webcasata-elementor-addons' ),
					'external' => __( 'External URL', 'webcasata-elementor-addons' ),
				],
				'condition' => [ 'video_type' => 'hosted' ],
			]
		);

		$this->add_control(
			'hosted_file',
			[
				'label'      => __( 'Video File', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'video',
				'dynamic'    => [ 'active' => true ],
				'condition'  => [
					'video_type'    => 'hosted',
					'hosted_source' => 'library',
				],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label'       => __( 'Video URL', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => 'https://example.com/video.mp4',
				'label_block' => true,
				'condition'   => [
					'video_type'    => 'hosted',
					'hosted_source' => 'external',
				],
			]
		);

		$this->add_control(
			'start_time',
			[
				'label'     => __( 'Start Time (Sec)', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'default'   => 0,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'end_time',
			[
				'label'       => __( 'End Time (Sec)', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'description' => __( 'Not supported by Vimeo.', 'webcasata-elementor-addons' ),
			]
		);

		$this->add_control(
			'video_style',
			[
				'label'     => __( 'Video Style', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'popup',
				'options'   => [
					'popup'  => __( 'Popup', 'webcasata-elementor-addons' ),
					'inline' => __( 'Inline', 'webcasata-elementor-addons' ),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label'   => __( 'Button Style', 'webcasata-elementor-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon'      => __( 'Icon', 'webcasata-elementor-addons' ),
					'icon_text' => __( 'Icon + Text', 'webcasata-elementor-addons' ),
					'text'      => __( 'Text', 'webcasata-elementor-addons' ),
				],
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'     => __( 'Button Text', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Watch video', 'webcasata-elementor-addons' ),
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'button_style' => [ 'text', 'icon_text' ] ],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label'       => __( 'Button Icon', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'description' => __( 'Leave empty to use the built-in play icon (its colour is set in Style).', 'webcasata-elementor-addons' ),
				'condition'   => [ 'button_style' => [ 'icon', 'icon_text' ] ],
			]
		);

		$this->add_control(
			'glow',
			[
				'label'        => __( 'Active Glow', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'webcasata-elementor-addons' ),
				'label_off'    => __( 'No', 'webcasata-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'glow_type',
			[
				'label'     => __( 'Glow Animation Type', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ripple',
				'options'   => [
					'ripple' => __( 'Ripple', 'webcasata-elementor-addons' ),
					'pulse'  => __( 'Pulse', 'webcasata-elementor-addons' ),
					'glow'   => __( 'Glow', 'webcasata-elementor-addons' ),
				],
				'condition' => [ 'glow' => 'yes' ],
			]
		);

		$this->add_control(
			'options_heading',
			[
				'label'     => __( 'Video Options', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Auto Play', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'webcasata-elementor-addons' ),
				'label_off'    => __( 'No', 'webcasata-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'Unmuted videos will not auto play in some browsers. Inline: the video is loaded and played when the widget scrolls into view. Popup: the video plays as soon as the popup opens (it is always started by a click).', 'webcasata-elementor-addons' ),
			]
		);

		foreach (
			[
				'mute'     => __( 'Mute', 'webcasata-elementor-addons' ),
				'loop'     => __( 'Loop', 'webcasata-elementor-addons' ),
				'controls' => __( 'Player Control', 'webcasata-elementor-addons' ),
			] as $id => $label
		) {
			$this->add_control(
				$id,
				[
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'webcasata-elementor-addons' ),
					'label_off'    => __( 'No', 'webcasata-elementor-addons' ),
					'return_value' => 'yes',
					'default'      => '',
				]
			);
		}

		$this->add_control(
			'privacy',
			[
				'label'        => __( 'Privacy Mode', 'webcasata-elementor-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'webcasata-elementor-addons' ),
				'label_off'    => __( 'No', 'webcasata-elementor-addons' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'YouTube: uses youtube-nocookie.com. Vimeo: asks the player not to track.', 'webcasata-elementor-addons' ),
				'condition'    => [ 'video_type' => [ 'youtube', 'vimeo' ] ],
			]
		);

		$this->add_control(
			'video_title',
			[
				'label'       => __( 'Video Title (for screen readers)', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => __( 'Play video', 'webcasata-elementor-addons' ),
				'label_block' => true,
				'separator'   => 'before',
			]
		);

		$this->end_controls_section();
	}

	private function register_poster_controls() {
		$this->start_controls_section(
			'section_poster',
			[
				'label' => __( 'Poster Image', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'poster_source',
			[
				'label'       => __( 'Poster', 'webcasata-elementor-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'auto',
				'options'     => [
					'auto'   => __( 'Automatic (YouTube / Vimeo thumbnail)', 'webcasata-elementor-addons' ),
					'custom' => __( 'Custom image', 'webcasata-elementor-addons' ),
					'none'   => __( 'None (dark box)', 'webcasata-elementor-addons' ),
				],
				'description' => __( 'The poster is only an image. The video itself is not in the page until it is played.', 'webcasata-elementor-addons' ),
			]
		);

		$this->add_control(
			'poster_image',
			[
				'label'     => __( 'Image', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'poster_source' => 'custom' ],
			]
		);

		$this->add_control(
			'poster_quality',
			[
				'label'     => __( 'YouTube Thumbnail Size', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'maxresdefault',
				'options'   => [
					'maxresdefault' => __( 'Maximum (1280 px)', 'webcasata-elementor-addons' ),
					'sddefault'     => __( 'Standard (640 px)', 'webcasata-elementor-addons' ),
					'hqdefault'     => __( 'High (480 px)', 'webcasata-elementor-addons' ),
				],
				'condition' => [
					'poster_source' => 'auto',
					'video_type'    => 'youtube',
				],
			]
		);

		$this->end_controls_section();
	}

	/* ------------------------------------------------------------------
	 * Style tab
	 * ------------------------------------------------------------------ */

	private function register_style_frame() {
		$this->start_controls_section(
			'style_frame',
			[
				'label' => __( 'Video Box', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'ratio',
			[
				'label'                => __( 'Aspect Ratio', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => '169',
				'options'              => [
					'169' => '16:9',
					'219' => '21:9',
					'43'  => '4:3',
					'32'  => '3:2',
					'11'  => '1:1',
					'916' => '9:16',
				],
				'selectors_dictionary' => [
					'169' => '--wv-ratio: 16 / 9; --wv-rn: 1.7778;',
					'219' => '--wv-ratio: 21 / 9; --wv-rn: 2.3333;',
					'43'  => '--wv-ratio: 4 / 3; --wv-rn: 1.3333;',
					'32'  => '--wv-ratio: 3 / 2; --wv-rn: 1.5;',
					'11'  => '--wv-ratio: 1 / 1; --wv-rn: 1;',
					'916' => '--wv-ratio: 9 / 16; --wv-rn: 0.5625;',
				],
				'selectors'            => [ $this->W( ' .wv-wrap' ) => '{{VALUE}}' ],
			]
		);

		$this->add_responsive_control(
			'frame_radius',
			[
				'label'      => __( 'Border Radius', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					$this->W( ' .wv-frame' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'frame_border',
				'selector'  => $this->W( ' .wv-frame' ),
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'frame_shadow',
				'selector' => $this->W( ' .wv-frame' ),
			]
		);

		$this->add_control(
			'poster_zoom',
			[
				'label'     => __( 'Poster Zoom on Hover', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [ 'size' => 1 ],
				'range'     => [ 'px' => [ 'min' => 1, 'max' => 1.3, 'step' => 0.01 ] ],
				'selectors' => [ $this->W( ' .wv-frame:hover .wv-poster' ) => 'transform: scale({{SIZE}});' ],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	private function register_style_overlay() {
		$this->start_controls_section(
			'style_overlay',
			[
				'label' => __( 'Overlay', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'ov_tabs' );

		$this->start_controls_tab( 'ov_tab_n', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'ov',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => $this->W( ' .wv-overlay' ),
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'ov_tab_h', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'ov_h',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => $this->W( ' .wv-frame:hover .wv-overlay' ),
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	private function register_style_button() {
		$this->start_controls_section(
			'style_button',
			[
				'label' => __( 'Play Button', 'webcasata-elementor-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'btn_position',
			[
				'label'                => __( 'Position', 'webcasata-elementor-addons' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'center',
				'options'              => [
					'center'        => __( 'Center', 'webcasata-elementor-addons' ),
					'top-left'      => __( 'Top left', 'webcasata-elementor-addons' ),
					'top-right'     => __( 'Top right', 'webcasata-elementor-addons' ),
					'bottom-left'   => __( 'Bottom left', 'webcasata-elementor-addons' ),
					'bottom-center' => __( 'Bottom center', 'webcasata-elementor-addons' ),
					'bottom-right'  => __( 'Bottom right', 'webcasata-elementor-addons' ),
				],
				'selectors_dictionary' => [
					'center'        => 'justify-content: center; align-items: center;',
					'top-left'      => 'justify-content: flex-start; align-items: flex-start;',
					'top-right'     => 'justify-content: flex-end; align-items: flex-start;',
					'bottom-left'   => 'justify-content: flex-start; align-items: flex-end;',
					'bottom-center' => 'justify-content: center; align-items: flex-end;',
					'bottom-right'  => 'justify-content: flex-end; align-items: flex-end;',
				],
				'selectors'            => [ $this->W( ' .wv-trigger' ) => '{{VALUE}}' ],
			]
		);

		$this->add_responsive_control(
			'btn_icon_size',
			[
				'label'      => __( 'Icon Size', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range'      => [ 'px' => [ 'min' => 16, 'max' => 240 ], 'em' => [ 'min' => 1, 'max' => 15 ] ],
				'default'    => [ 'size' => 80, 'unit' => 'px' ],
				'selectors'  => [ $this->W( ' .wv-icon' ) => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
				'condition'  => [ 'button_style' => [ 'icon', 'icon_text' ] ],
			]
		);

		$this->start_controls_tabs( 'btn_tabs' );

		$this->start_controls_tab( 'btn_tab_n', [ 'label' => __( 'Normal', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'btn_color',
			[
				'label'     => __( 'Icon / Text Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ $this->W( ' .wv-play' ) => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'btn_bg_c',
			[
				'label'     => __( 'Background', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wv-play' ) => 'background-color: {{VALUE}};' ],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab( 'btn_tab_h', [ 'label' => __( 'Hover', 'webcasata-elementor-addons' ) ] );
		$this->add_control(
			'btn_color_h',
			[
				'label'     => __( 'Icon / Text Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wv-frame:hover .wv-play' ) => 'color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'btn_bg_c_h',
			[
				'label'     => __( 'Background', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $this->W( ' .wv-frame:hover .wv-play' ) => 'background-color: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'btn_scale_h',
			[
				'label'     => __( 'Scale on Hover', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [ 'size' => 1.08 ],
				'range'     => [ 'px' => [ 'min' => 0.8, 'max' => 1.4, 'step' => 0.01 ] ],
				'selectors' => [ $this->W( ' .wv-frame:hover .wv-play' ) => 'transform: scale({{SIZE}});' ],
			]
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'btn_padding',
			[
				'label'      => __( 'Padding', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%', 'custom' ],
				'selectors'  => [
					$this->W( ' .wv-play' ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'btn_radius',
			[
				'label'      => __( 'Border Radius', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors'  => [
					$this->W( ' .wv-play' ) => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'btn_border',
				'selector' => $this->W( ' .wv-play' ),
			]
		);

		$this->add_responsive_control(
			'btn_gap',
			[
				'label'      => __( 'Space Between Icon and Text', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'max' => 60 ] ],
				'selectors'  => [ $this->W( ' .wv-play' ) => 'gap: {{SIZE}}{{UNIT}};' ],
				'condition'  => [ 'button_style' => 'icon_text' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'btn_typo',
				'selector'  => $this->W( ' .wv-label' ),
				'condition' => [ 'button_style' => [ 'text', 'icon_text' ] ],
			]
		);

		$this->end_controls_section();
	}

	private function register_style_glow() {
		$on = [ 'glow' => 'yes' ];

		$this->start_controls_section(
			'style_glow',
			[
				'label'     => __( 'Glow', 'webcasata-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => $on,
			]
		);

		$this->add_control(
			'glow_color',
			[
				'label'     => __( 'Glow Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ $this->W( ' .wv-wrap' ) => '--wv-glow: {{VALUE}};' ],
				'condition' => $on,
			]
		);

		$this->add_control(
			'glow_size',
			[
				'label'      => __( 'Glow Size', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 4, 'max' => 80 ] ],
				'default'    => [ 'size' => 36, 'unit' => 'px' ],
				'selectors'  => [ $this->W( ' .wv-wrap' ) => '--wv-glow-size: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->add_control(
			'glow_speed',
			[
				'label'      => __( 'Animation Duration (s)', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0.5, 'max' => 6, 'step' => 0.1 ] ],
				'default'    => [ 'size' => 2, 'unit' => 'px' ],
				'selectors'  => [ $this->W( ' .wv-wrap' ) => '--wv-glow-dur: {{SIZE}}s;' ],
				'condition'  => $on,
			]
		);

		$this->end_controls_section();
	}

	private function register_style_popup() {
		$on = [ 'video_style' => 'popup' ];

		$this->start_controls_section(
			'style_popup',
			[
				'label'     => __( 'Popup', 'webcasata-elementor-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => $on,
			]
		);

		$this->add_control(
			'popup_width',
			[
				'label'      => __( 'Maximum Width', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range'      => [ 'px' => [ 'min' => 300, 'max' => 1600 ], 'vw' => [ 'min' => 30, 'max' => 100 ] ],
				'default'    => [ 'size' => 960, 'unit' => 'px' ],
				'selectors'  => [ $this->W( ' .wv-wrap' ) => '--wv-modal-w: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->add_control(
			'popup_backdrop',
			[
				'label'     => __( 'Backdrop Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.85)',
				'selectors' => [ $this->W( ' .wv-wrap' ) => '--wv-modal-bg: {{VALUE}};' ],
				'condition' => $on,
			]
		);

		$this->add_control(
			'popup_close',
			[
				'label'     => __( 'Close Button Color', 'webcasata-elementor-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ $this->W( ' .wv-wrap' ) => '--wv-close-c: {{VALUE}};' ],
				'condition' => $on,
			]
		);

		$this->add_control(
			'popup_radius',
			[
				'label'      => __( 'Video Corner Radius', 'webcasata-elementor-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'selectors'  => [ $this->W( ' .wv-wrap' ) => '--wv-modal-r: {{SIZE}}{{UNIT}};' ],
				'condition'  => $on,
			]
		);

		$this->end_controls_section();
	}

	/* ==================================================================
	 * Render
	 * ================================================================== */

	/**
	 * Everything the script needs, or null when the widget has no usable video.
	 */
	public function build_config( array $s ) {
		$type  = isset( $s['video_type'] ) ? $s['video_type'] : 'youtube';
		$start = isset( $s['start_time'] ) ? (int) $s['start_time'] : 0;
		$end   = isset( $s['end_time'] ) ? (int) $s['end_time'] : 0;
		$flag  = function ( $k ) use ( $s ) {
			return isset( $s[ $k ] ) && 'yes' === $s[ $k ];
		};

		$opts = [
			'mute'     => $flag( 'mute' ),
			'loop'     => $flag( 'loop' ),
			'controls' => $flag( 'controls' ),
			'privacy'  => $flag( 'privacy' ),
			'start'    => $start,
			'end'      => $end,
		];

		$poster      = '';
		$poster_fb   = '';
		$poster_src  = isset( $s['poster_source'] ) ? $s['poster_source'] : 'auto';
		$custom      = ( 'custom' === $poster_src && ! empty( $s['poster_image']['url'] ) ) ? esc_url_raw( $s['poster_image']['url'] ) : '';

		if ( 'youtube' === $type ) {
			$id = self::parse_youtube_id( isset( $s['link'] ) ? $s['link'] : '' );
			if ( '' === $id ) {
				return null;
			}
			$src = self::embed_url( 'youtube', $opts + [ 'id' => $id ] );
			if ( 'auto' === $poster_src ) {
				$q          = isset( $s['poster_quality'] ) && in_array( $s['poster_quality'], [ 'maxresdefault', 'sddefault', 'hqdefault' ], true ) ? $s['poster_quality'] : 'maxresdefault';
				$poster     = 'https://i.ytimg.com/vi/' . $id . '/' . $q . '.jpg';
				$poster_fb  = 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
			}
		} elseif ( 'vimeo' === $type ) {
			list( $id, $hash ) = self::parse_vimeo( isset( $s['link'] ) ? $s['link'] : '' );
			if ( '' === $id ) {
				return null;
			}
			$src = self::embed_url( 'vimeo', $opts + [ 'id' => $id, 'hash' => $hash ] );
			if ( 'auto' === $poster_src ) {
				$poster = $this->vimeo_thumbnail( $id, $hash );
			}
		} else {
			$file = '';
			if ( isset( $s['hosted_source'] ) && 'external' === $s['hosted_source'] ) {
				$file = isset( $s['hosted_url'] ) ? trim( (string) $s['hosted_url'] ) : '';
			} elseif ( ! empty( $s['hosted_file']['url'] ) ) {
				$file = $s['hosted_file']['url'];
			}
			$file = esc_url_raw( $file );
			if ( '' === $file ) {
				return null;
			}
			$src = self::hosted_url( $file, $start, $end );
		}

		if ( '' !== $custom ) {
			$poster    = $custom;
			$poster_fb = '';
		}

		return [
			'type'     => $type,
			'style'    => ( isset( $s['video_style'] ) && 'inline' === $s['video_style'] ) ? 'inline' : 'popup',
			'src'      => $src,
			'autoplay' => $flag( 'autoplay' ),
			'mute'     => $opts['mute'],
			'loop'     => $opts['loop'],
			'controls' => $opts['controls'],
			'title'    => isset( $s['video_title'] ) && '' !== trim( $s['video_title'] ) ? wp_strip_all_tags( $s['video_title'] ) : __( 'Play video', 'webcasata-elementor-addons' ),
			'poster'   => $poster,
			'posterFb' => $poster_fb,
		];
	}

	private function play_icon_markup( array $s ) {
		if ( ! empty( $s['button_icon']['url'] ) ) {
			return '<img class="wv-icon" src="' . esc_url( $s['button_icon']['url'] ) . '" alt="" loading="lazy" decoding="async">';
		}

		return '<svg class="wv-icon" viewBox="0 0 80 80" width="80" height="80" aria-hidden="true" focusable="false"><circle cx="40" cy="40" r="37" fill="none" stroke="currentColor" stroke-width="3"/><path d="M33 26v28l23-14z" fill="currentColor"/></svg>';
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$cfg = $this->build_config( $s );

		if ( null === $cfg ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="wv-empty">' . esc_html__( 'Add a valid video link (or file) in the Video section to see the player here.', 'webcasata-elementor-addons' ) . '</div>';
			}
			return;
		}

		$style = isset( $s['button_style'] ) && in_array( $s['button_style'], [ 'icon', 'text', 'icon_text' ], true ) ? $s['button_style'] : 'icon';
		$glow  = ( isset( $s['glow'] ) && 'yes' === $s['glow'] ) ? 'wv-glow wv-glow-' . ( in_array( isset( $s['glow_type'] ) ? $s['glow_type'] : '', [ 'ripple', 'pulse', 'glow' ], true ) ? $s['glow_type'] : 'ripple' ) : '';
		$text  = isset( $s['button_text'] ) ? trim( wp_strip_all_tags( $s['button_text'] ) ) : '';

		$classes = 'wv-wrap wv-style-' . $cfg['style'] . ' wv-type-' . $cfg['type'];
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" data-wv="<?php echo esc_attr( wp_json_encode( $cfg ) ); ?>">
			<div class="wv-frame">
				<?php if ( '' !== $cfg['poster'] ) : ?>
					<img class="wv-poster" src="<?php echo esc_url( $cfg['poster'] ); ?>"<?php echo '' !== $cfg['posterFb'] ? ' data-fallback="' . esc_url( $cfg['posterFb'] ) . '"' : ''; ?> alt="" loading="lazy" decoding="async">
				<?php endif; ?>
				<div class="wv-overlay"></div>
				<button type="button" class="wv-trigger" aria-label="<?php echo esc_attr( $cfg['title'] ); ?>"<?php echo 'popup' === $cfg['style'] ? ' aria-haspopup="dialog"' : ''; ?>>
					<span class="wv-play wv-bs-<?php echo esc_attr( $style ); ?> <?php echo esc_attr( $glow ); ?>">
						<?php
						if ( 'text' !== $style ) {
							echo $this->play_icon_markup( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts / static SVG.
						}
						if ( 'icon' !== $style && '' !== $text ) {
							echo '<span class="wv-label">' . esc_html( $text ) . '</span>';
						}
						?>
					</span>
				</button>
			</div>
			<noscript><a class="wv-noscript" href="<?php echo esc_url( 'hosted' === $cfg['type'] ? $cfg['src'] : preg_replace( '#/embed/#', '/watch?v=', strtok( $cfg['src'], '?' ) ) ); ?>"><?php esc_html_e( 'Watch the video', 'webcasata-elementor-addons' ); ?></a></noscript>
		</div>
		<?php
	}
}
