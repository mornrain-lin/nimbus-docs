<?php
/**
 * Nimbus Docs 自定义器配置。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nimbus_option_defaults' ) ) {
	/**
	 * 主题全部设置项默认值。
	 *
	 * @return array<string,mixed>
	 */
	function nimbus_option_defaults() {
		return array(
			'accent_color'        => '',
			'base_background'     => '',
			'sidebar_width'       => 280,
			'content_width'       => 780,
			'wide_width'          => 1120,
			'toc_pages'           => '',
			'toc_default_collapsed' => false,
			'code_copy_button'    => true,
			'heading_anchors'     => true,
			'feedback_enabled'    => true,
			'pager_enabled'       => true,
			'breadcrumb_enabled'  => true,
			'excerpt_length'      => 32,
			'footer_widgets'      => true,
		);
	}
}

if ( ! function_exists( 'nimbus_get_option' ) ) {
	/**
	 * 读取主题设置。
	 *
	 * @param string $key     设置键名。
	 * @param mixed  $default 自定义默认值。
	 * @return mixed
	 */
function nimbus_get_option( $key, $default = null ) {
	$defaults = nimbus_option_defaults();

	if ( null !== $default ) {
		$defaults[ $key ] = $default;
	}

	if ( ! isset( $defaults[ $key ] ) ) {
		return $default;
	}

	$value = get_theme_mod( 'nimbus_' . $key, $defaults[ $key ] );

	/**
	 * 过滤任意主题设置项的最终值。
	 *
	 * 例：add_filter( 'nimbus_option_pager_enabled', '__return_false' );
	 *
	 * @param mixed  $value 设置值。
	 * @param string $key   设置键名。
	 */
	return apply_filters( 'nimbus_option_' . $key, $value, $key );
}
}

if ( ! function_exists( 'nimbus_build_dynamic_css' ) ) {
	/**
	 * 把设置转为 CSS 变量。
	 *
	 * @return string
	 */
	function nimbus_build_dynamic_css() {
		$vars = array();

		$accent = nimbus_get_option( 'accent_color' );

		if ( $accent ) {
			$accent = sanitize_hex_color( $accent );

			if ( $accent ) {
				$vars['--nb-accent'] = $accent;
				$vars['--nb-accent-soft'] = nimbus_hex_to_rgba( $accent, 0.08 );
				$vars['--nb-focus'] = $accent;
			}
		}

		$base = nimbus_get_option( 'base_background' );

		if ( $base ) {
			$base = sanitize_hex_color( $base );

			if ( $base ) {
				$vars['--nb-bg'] = $base;
			}
		}

		$sidebar_width = (int) nimbus_get_option( 'sidebar_width' );

		if ( $sidebar_width >= 200 && $sidebar_width <= 420 ) {
			$vars['--nb-sidebar-width'] = $sidebar_width . 'px';
		}

		$content_width = (int) nimbus_get_option( 'content_width' );

		if ( $content_width >= 560 && $content_width <= 1000 ) {
			$vars['--nb-content-width'] = $content_width . 'px';
		}

		$wide_width = (int) nimbus_get_option( 'wide_width' );

		if ( $wide_width >= 900 && $wide_width <= 1800 ) {
			$vars['--nb-wide-width'] = $wide_width . 'px';
		}

		if ( empty( $vars ) ) {
			return '';
		}

		$declarations = '';

		foreach ( $vars as $name => $value ) {
			$declarations .= $name . ':' . $value . ';';
		}

		return ':root{' . $declarations . '}';
	}
}

if ( ! function_exists( 'nimbus_hex_to_rgba' ) ) {
	/**
	 * 十六进制颜色转 rgba。
	 *
	 * @param string $hex   颜色值。
	 * @param float  $alpha 透明度。
	 * @return string
	 */
	function nimbus_hex_to_rgba( $hex, $alpha = 0.1 ) {
		$hex = ltrim( (string) $hex, '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return 'rgba(47, 111, 235, ' . (float) $alpha . ')';
		}

		return 'rgba(' . hexdec( substr( $hex, 0, 2 ) ) . ', ' . hexdec( substr( $hex, 2, 2 ) ) . ', ' . hexdec( substr( $hex, 4, 2 ) ) . ', ' . (float) $alpha . ')';
	}
}

if ( ! function_exists( 'nimbus_customize_register' ) ) {
	/**
	 * 注册 Customizer 面板与设置项。
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer 实例。
	 * @return void
	 */
	function nimbus_customize_register( $wp_customize ) {
		$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.nb-brand__name',
					'render_callback' => 'nimbus_customize_blogname',
				)
			);
		}

		$wp_customize->add_panel(
			'nimbus_panel',
			array(
				'title'       => esc_html__( '主题设置', 'nimbus-docs' ),
				'description' => esc_html__( 'Nimbus Docs 的目录、布局与阅读选项。', 'nimbus-docs' ),
				'priority'    => 20,
			)
		);

		/* ------------------------------------------------------------------
		 * 分区一：配色
		 * ------------------------------------------------------------------ */
		$wp_customize->add_section(
			'nimbus_colors',
			array(
				'title' => esc_html__( '配色', 'nimbus-docs' ),
				'panel' => 'nimbus_panel',
			)
		);

		$wp_customize->add_setting(
			'nimbus_accent_color',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_accent_color',
			array(
				'label'       => esc_html__( '强调色', 'nimbus-docs' ),
				'description' => esc_html__( '留空使用主题默认的蓝色 #2f6feb。', 'nimbus-docs' ),
				'section'     => 'nimbus_colors',
				'type'        => 'color',
			)
		);

		$wp_customize->add_setting(
			'nimbus_base_background',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_base_background',
			array(
				'label'   => esc_html__( '页面底色', 'nimbus-docs' ),
				'section' => 'nimbus_colors',
				'type'    => 'color',
			)
		);

		/* ------------------------------------------------------------------
		 * 分区二：布局
		 * ------------------------------------------------------------------ */
		$wp_customize->add_section(
			'nimbus_layout',
			array(
				'title'       => esc_html__( '布局', 'nimbus-docs' ),
				'description' => esc_html__( '调整目录栏宽度与正文宽度，输出为 CSS 变量。', 'nimbus-docs' ),
				'panel'       => 'nimbus_panel',
			)
		);

		$wp_customize->add_setting(
			'nimbus_sidebar_width',
			array(
				'default'           => 280,
				'sanitize_callback' => 'nimbus_sanitize_number',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_sidebar_width',
			array(
				'label'       => esc_html__( '目录栏宽度（像素）', 'nimbus-docs' ),
				'section'     => 'nimbus_layout',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 200,
					'max'  => 420,
					'step' => 10,
				),
			)
		);

		$wp_customize->add_setting(
			'nimbus_content_width',
			array(
				'default'           => 780,
				'sanitize_callback' => 'nimbus_sanitize_number',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_content_width',
			array(
				'label'   => esc_html__( '正文宽度（像素）', 'nimbus-docs' ),
				'section' => 'nimbus_layout',
				'type'    => 'number',
				'input_attrs' => array(
					'min'  => 560,
					'max'  => 1000,
					'step' => 10,
				),
			)
		);

		$wp_customize->add_setting(
			'nimbus_wide_width',
			array(
				'default'           => 1120,
				'sanitize_callback' => 'nimbus_sanitize_number',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_wide_width',
			array(
				'label'       => esc_html__( '宽元素宽度（像素）', 'nimbus-docs' ),
				'description' => esc_html__( 'alignwide 元素与代码块使用的宽度。', 'nimbus-docs' ),
				'section'     => 'nimbus_layout',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 900,
					'max'  => 1800,
					'step' => 10,
				),
			)
		);

		/* ------------------------------------------------------------------
		 * 分区三：目录
		 * ------------------------------------------------------------------ */
		$wp_customize->add_section(
			'nimbus_toc',
			array(
				'title' => esc_html__( '目录', 'nimbus-docs' ),
				'panel' => 'nimbus_panel',
			)
		);

		$wp_customize->add_setting(
			'nimbus_toc_pages',
			array(
				'default'           => '',
				'sanitize_callback' => 'nimbus_sanitize_id_list',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_toc_pages',
			array(
				'label'       => esc_html__( '指定目录页面 ID', 'nimbus-docs' ),
				'description' => esc_html__( '英文逗号分隔的页面 ID，例如 12,15,18。留空时自动列出全部已发布页面，并按父子层级与菜单顺序排列。', 'nimbus-docs' ),
				'section'     => 'nimbus_toc',
				'type'        => 'text',
			)
		);

		$wp_customize->add_setting(
			'nimbus_toc_default_collapsed',
			array(
				'default'           => false,
				'sanitize_callback' => 'nimbus_sanitize_checkbox',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_toc_default_collapsed',
			array(
				'label'       => esc_html__( '目录默认折叠', 'nimbus-docs' ),
				'description' => esc_html__( '访客的选择会记入浏览器 localStorage，优先于此处的默认值。', 'nimbus-docs' ),
				'section'     => 'nimbus_toc',
				'type'        => 'checkbox',
			)
		);

		/* ------------------------------------------------------------------
		 * 分区四：阅读增强
		 * ------------------------------------------------------------------ */
		$wp_customize->add_section(
			'nimbus_reading',
			array(
				'title' => esc_html__( '阅读增强', 'nimbus-docs' ),
				'panel' => 'nimbus_panel',
			)
		);

		$nimbus_toggles = array(
			'code_copy_button'   => array(
				'label' => esc_html__( '代码块显示复制按钮', 'nimbus-docs' ),
			),
			'heading_anchors'    => array(
				'label' => esc_html__( '标题显示锚点链接', 'nimbus-docs' ),
			),
			'feedback_enabled'   => array(
				'label' => esc_html__( '显示文档反馈按钮', 'nimbus-docs' ),
			),
			'pager_enabled'      => array(
				'label' => esc_html__( '显示上下节导航', 'nimbus-docs' ),
			),
			'breadcrumb_enabled' => array(
				'label' => esc_html__( '显示文档路径面包屑', 'nimbus-docs' ),
			),
			'footer_widgets'     => array(
				'label' => esc_html__( '显示页脚 Widget 区域', 'nimbus-docs' ),
			),
		);

		foreach ( $nimbus_toggles as $key => $field ) {
			$wp_customize->add_setting(
				'nimbus_' . $key,
				array(
					'default'           => true,
					'sanitize_callback' => 'nimbus_sanitize_checkbox',
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				'nimbus_' . $key,
				array(
					'label'   => $field['label'],
					'section' => 'nimbus_reading',
					'type'    => 'checkbox',
				)
			);
		}

		$wp_customize->add_setting(
			'nimbus_excerpt_length',
			array(
				'default'           => 32,
				'sanitize_callback' => 'nimbus_sanitize_number',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'nimbus_excerpt_length',
			array(
				'label'   => esc_html__( '摘要长度（词）', 'nimbus-docs' ),
				'section' => 'nimbus_reading',
				'type'    => 'number',
				'input_attrs' => array(
					'min'  => 8,
					'max'  => 100,
					'step' => 1,
				),
			)
		);
	}
}
add_action( 'customize_register', 'nimbus_customize_register' );

if ( ! function_exists( 'nimbus_customize_blogname' ) ) {
	/**
	 * 站点标题即时刷新。
	 *
	 * @return void
	 */
	function nimbus_customize_blogname() {
		bloginfo( 'name', 'display' );
	}
}

if ( ! function_exists( 'nimbus_customize_preview_js' ) ) {
	/**
	 * 加载 Customizer 预览脚本。
	 *
	 * @return void
	 */
	function nimbus_customize_preview_js() {
		wp_enqueue_script(
			'nimbus-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			nimbus_asset_version( 'assets/js/customizer-preview.js' ),
			true
		);
	}
}
add_action( 'customize_preview_init', 'nimbus_customize_preview_js' );

if ( ! function_exists( 'nimbus_sanitize_checkbox' ) ) {
	/**
	 * 复选框清洗。
	 *
	 * @param mixed $checked 输入值。
	 * @return bool
	 */
	function nimbus_sanitize_checkbox( $checked ) {
		return ( isset( $checked ) && true === (bool) $checked );
	}
}

if ( ! function_exists( 'nimbus_sanitize_number' ) ) {
	/**
	 * 整数清洗。
	 *
	 * @param mixed $value 输入值。
	 * @return int
	 */
	function nimbus_sanitize_number( $value ) {
		return (int) $value;
	}
}

if ( ! function_exists( 'nimbus_sanitize_id_list' ) ) {
	/**
	 * 逗号分隔的正整数 ID 列表清洗。
	 *
	 * @param mixed $value 输入值。
	 * @return string
	 */
	function nimbus_sanitize_id_list( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$parts = explode( ',', $value );
		$ids   = array();

		foreach ( $parts as $part ) {
			$id = absint( trim( $part ) );

			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		return implode( ',', array_unique( $ids ) );
	}
}
