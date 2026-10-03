<?php
/**
 * Nimbus Docs 主题函数入口。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'NIMBUS_VERSION' ) ) {
	define( 'NIMBUS_VERSION', '1.0.0' );
}

if ( ! defined( 'NIMBUS_TEXT_DOMAIN' ) ) {
	define( 'NIMBUS_TEXT_DOMAIN', 'nimbus-docs' );
}

/**
 * 读取主题资源的版本号，用于前端缓存 busting。
 *
 * 优先使用文件的最后修改时间，文件不可读时回落到主题版本号。
 *
 * @param string $relative_path 相对主题根目录的路径。
 * @return string
 */
function nimbus_asset_version( $relative_path ) {
	$file = get_template_directory() . '/' . ltrim( $relative_path, '/' );

	if ( file_exists( $file ) ) {
		$mtime = filemtime( $file );

		if ( $mtime ) {
			return (string) $mtime;
		}
	}

	return NIMBUS_VERSION;
}

require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/toc.php';
require_once get_template_directory() . '/inc/feedback.php';
require_once get_template_directory() . '/inc/customizer.php';


if ( ! function_exists( 'nimbus_setup' ) ) {
	/**
	 * 声明主题基础能力。
	 *
	 * @return void
	 */
	function nimbus_setup() {
		load_theme_textdomain( 'nimbus-docs', get_template_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( 'responsive-embeds' );

		// 文档主题的核心：宽对齐与全宽容器。
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-font-sizes' );

		add_theme_support( 'editor-styles' );

		// 允许编辑器输出全宽块（alignfull）。
		add_theme_support( 'editor-color-palette' );

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'navigation-widgets',
			)
		);

		add_theme_support(
			'custom-logo',
			array(
				'height'      => 40,
				'width'       => 180,
				'flex-height' => true,
				'flex-width'  => true,
				'header-text' => array( 'site-title' ),
			)
		);

		add_theme_support(
			'post-formats',
			array( 'aside', 'gallery', 'image', 'quote', 'status', 'video', 'audio', 'link' )
		);

		// 全宽模板：让代码块与宽图可以铺满。
		add_theme_support(
			'align-wide',
			array(
				'contentSize' => 780,
				'wideSize'    => 1120,
			)
		);

		add_editor_style( 'assets/css/editor-style.css' );

		add_image_size( 'nimbus-card', 640, 400, true );

		register_nav_menus(
			array(
				'primary' => esc_html__( '主导航', 'nimbus-docs' ),
				'footer'  => esc_html__( '页脚导航', 'nimbus-docs' ),
			)
		);
	}
}
add_action( 'after_setup_theme', 'nimbus_setup' );

if ( ! function_exists( 'nimbus_content_width' ) ) {
	/**
	 * 设置正文宽度。
	 *
	 * @global int $content_width
	 * @return void
	 */
	function nimbus_content_width() {
		$width = 780;

		/**
		 * 过滤正文宽度。
		 *
		 * @param int $width 宽度（像素）。
		 */
		$width = (int) apply_filters( 'nimbus_content_width', $width );

		$GLOBALS['content_width'] = $width;
	}
}
add_action( 'after_setup_theme', 'nimbus_content_width', 0 );


if ( ! function_exists( 'nimbus_widgets_init' ) ) {
	/**
	 * 注册侧栏 1 个 + 页脚 2 个 Widget 区域。
	 *
	 * @return void
	 */
	function nimbus_widgets_init() {
		$defaults = array(
			'before_widget' => '<section id="%1$s" class="nb-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="nb-widget__title">',
			'after_title'   => '</h2>',
		);

		register_sidebar(
			array_merge(
				$defaults,
				array(
					'name'        => esc_html__( '侧栏 1（目录下方）', 'nimbus-docs' ),
					'id'          => 'sidebar-1',
					'description' => esc_html__( '显示在左侧目录树下方。', 'nimbus-docs' ),
				)
			)
		);

		for ( $i = 1; $i <= 2; $i++ ) {
			register_sidebar(
				array_merge(
					$defaults,
					array(
						/* translators: %d：页脚栏序号。 */
						'name'        => sprintf( esc_html__( '页脚 %d', 'nimbus-docs' ), $i ),
						'id'          => 'footer-' . $i,
						/* translators: %d：页脚栏序号。 */
						'description' => sprintf( esc_html__( '页脚第 %d 栏。', 'nimbus-docs' ), $i ),
					)
				)
			);
		}
	}
}
add_action( 'widgets_init', 'nimbus_widgets_init' );


if ( ! function_exists( 'nimbus_scripts' ) ) {
	/**
	 * 加载前端样式与脚本。
	 *
	 * @return void
	 */
	function nimbus_scripts() {
		wp_enqueue_style(
			'nimbus-style',
			get_stylesheet_uri(),
			array(),
			nimbus_asset_version( 'style.css' )
		);

		wp_style_add_data( 'nimbus-style', 'rtl', 'replace' );

		wp_enqueue_script(
			'nimbus-toc',
			get_template_directory_uri() . '/assets/js/toc.js',
			array(),
			nimbus_asset_version( 'assets/js/toc.js' ),
			true
		);

		wp_localize_script(
			'nimbus-toc',
			'nimbusL10n',
			array(
				'storageKey'    => nimbus_toc_storage_key(),
				'toggleSidebar' => esc_html__( '切换目录侧栏', 'nimbus-docs' ),
				'copyCode'      => esc_html__( '复制', 'nimbus-docs' ),
				'copied'        => esc_html__( '已复制', 'nimbus-docs' ),
				'copyFailed'    => esc_html__( '复制失败', 'nimbus-docs' ),
				'expandLabel'   => esc_html__( '展开', 'nimbus-docs' ),
				'collapseLabel' => esc_html__( '收起', 'nimbus-docs' ),
				'onThisPage'    => esc_html__( '本页内容', 'nimbus-docs' ),
				'expandItem'    => esc_html__( '展开条目', 'nimbus-docs' ),
			)
		);

		// 代码复制只在有代码块的页面加载。
		if ( is_singular() ) {
			wp_enqueue_script(
				'nimbus-code-copy',
				get_template_directory_uri() . '/assets/js/code-copy.js',
				array( 'nimbus-toc' ),
				nimbus_asset_version( 'assets/js/code-copy.js' ),
				true
			);
		}

		// 反馈按钮只在单篇文章 / 页面加载。
		if ( is_singular( array( 'post', 'page' ) ) ) {
			wp_enqueue_script(
				'nimbus-feedback',
				get_template_directory_uri() . '/assets/js/feedback.js',
				array(),
				nimbus_asset_version( 'assets/js/feedback.js' ),
				true
			);

			wp_localize_script(
				'nimbus-feedback',
				'nimbusFeedback',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'nimbus_feedback' ),
					'postId'     => get_queried_object_id(),
					'savedText'  => esc_html__( '感谢反馈！', 'nimbus-docs' ),
					'failedText' => esc_html__( '反馈提交失败，请稍后重试。', 'nimbus-docs' ),
				)
			);
		}

		// 搜索高亮只在搜索结果页加载。
		if ( is_search() ) {
			wp_add_inline_script(
				'nimbus-toc',
				'window.nimbusSearchQuery = ' . wp_json_encode( get_search_query() ) . ';',
				'before'
			);
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'nimbus_scripts' );

if ( ! function_exists( 'nimbus_body_classes' ) ) {
	/**
	 * 为 <body> 追加状态类。
	 *
	 * @param array $classes 现有类名。
	 * @return array
	 */
	function nimbus_body_classes( $classes ) {
		$classes[] = 'nb-site';

		if ( is_singular( array( 'post', 'page' ) ) ) {
			$classes[] = 'nb-single-doc';
		}

		return $classes;
	}
}
add_filter( 'body_class', 'nimbus_body_classes' );

if ( ! function_exists( 'nimbus_excerpt_length' ) ) {
	/**
	 * 摘要长度。
	 *
	 * @param int $length 默认长度。
	 * @return int
	 */
	function nimbus_excerpt_length( $length ) {
		$custom = (int) nimbus_get_option( 'excerpt_length' );

		return $custom > 0 ? $custom : (int) $length;
	}
}
add_filter( 'excerpt_length', 'nimbus_excerpt_length' );

if ( ! function_exists( 'nimbus_excerpt_more' ) ) {
	/**
	 * 摘要省略符。
	 *
	 * @param string $more 默认省略符。
	 * @return string
	 */
	function nimbus_excerpt_more( $more ) {
		if ( is_admin() ) {
			return $more;
		}

		return '…';
	}
}
add_filter( 'excerpt_more', 'nimbus_excerpt_more' );

if ( ! function_exists( 'nimbus_pingback_header' ) ) {
	/**
	 * 输出pingback 链接。
	 *
	 * @return void
	 */
	function nimbus_pingback_header() {
		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}
}
add_action( 'wp_head', 'nimbus_pingback_header' );

if ( ! function_exists( 'nimbus_dynamic_css' ) ) {
	/**
	 * 输出 Customizer 生成的 CSS 变量。
	 *
	 * @return void
	 */
	function nimbus_dynamic_css() {
		$css = nimbus_build_dynamic_css();

		if ( '' === $css ) {
			return;
		}

		wp_add_inline_style( 'nimbus-style', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'nimbus_dynamic_css', 20 );

if ( ! function_exists( 'nimbus_code_copy_buttons' ) ) {
	/**
	 * 为文章内的 pre 代码块注入复制按钮容器。
	 *
	 * 与 the_content 过滤器分离：本函数只包一层容器，
	 * 真正的内容处理见 nimbus_filter_code_blocks()。
	 *
	 * @param string $content 文章内容。
	 * @return string
	 */
	function nimbus_code_copy_buttons( $content ) {
		if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( ! nimbus_get_option( 'code_copy_button' ) ) {
			return $content;
		}

		return nimbus_filter_code_blocks( $content );
	}
}
add_filter( 'the_content', 'nimbus_code_copy_buttons', 20 );

if ( ! function_exists( 'nimbus_woocommerce_declared' ) ) {
	/**
	 * 仅声明 WooCommerce 支持。
	 *
	 * @return void
	 */
	function nimbus_woocommerce_declared() {
		add_theme_support( 'woocommerce' );
	}
}
add_action( 'after_setup_theme', 'nimbus_woocommerce_declared' );

if ( ! function_exists( 'nimbus_notice_shortcode' ) ) {
	/**
	 * 提示框短代码。
	 *
	 * 用法：`[nimbus_notice type="warning" title="注意"]内容[/nimbus_notice]`
	 *
	 * @param array  $atts    短代码属性。
	 * @param string $content 内容。
	 * @return string
	 */
	function nimbus_notice_shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'type'  => 'tip',
				'title' => '',
			),
			$atts,
			'nimbus_notice'
		);

		$allowed = array( 'tip', 'warning', 'danger', 'success' );
		$type    = in_array( $atts['type'], $allowed, true ) ? $atts['type'] : 'tip';

		$titles = array(
			'tip'     => esc_html__( '提示', 'nimbus-docs' ),
			'warning' => esc_html__( '注意', 'nimbus-docs' ),
			'danger'  => esc_html__( '警告', 'nimbus-docs' ),
			'success' => esc_html__( '已完成', 'nimbus-docs' ),
		);

		$title = '' !== $atts['title'] ? $atts['title'] : $titles[ $type ];

		$html = '<div class="nb-notice nb-notice--' . esc_attr( $type ) . '">';
		$html .= '<strong class="nb-notice__title">' . esc_html( $title ) . '</strong>';
		$html .= wpautop( do_shortcode( $content ) );
		$html .= '</div>';

		return $html;
	}
}
add_shortcode( 'nimbus_notice', 'nimbus_notice_shortcode' );
