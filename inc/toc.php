<?php
/**
 * Nimbus Docs 目录树与代码块处理。
 *
 * 左侧目录树由两部分构成：
 * 1. 服务端输出的「页面树」：按父子层级列出所有文档页面
 * 2. 客户端 JS 追加的「页内锚点」：当前页面的h2 - h4 标题
 *
 * 折叠状态用 localStorage 记忆，键名按站点区分。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nimbus_toc_storage_key' ) ) {
	/**
	 * 目录折叠状态的 localStorage 键名。
	 *
	 * @return string
	 */
	function nimbus_toc_storage_key() {
		return 'nimbus-toc-' . substr( md5( home_url( '/' ) ), 0, 8 );
	}
}

if ( ! function_exists( 'nimbus_get_doc_pages' ) ) {
	/**
	 * 获取文档页面列表（按层级 + 菜单顺序）。
	 *
	 * 优先取 Customizer 中指定的页面 ID；缺省时取全部已发布页面。
	 *
	 * @return WP_Post[]
	 */
	function nimbus_get_doc_pages() {
		$configured = (string) nimbus_get_option( 'toc_pages' );
		$args       = array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		);

		if ( '' !== $configured ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $configured ) ) );

			if ( ! empty( $ids ) ) {
				$args['post__in'] = $ids;
				$args['orderby']  = 'post__in';
			}
		}

		$pages = get_posts( $args );

		/**
		 * 过滤左侧目录的页面列表。
		 *
		 * @param WP_Post[] $pages 页面列表。
		 */
		return apply_filters( 'nimbus_doc_pages', $pages );
	}
}

if ( ! function_exists( 'nimbus_build_page_tree' ) ) {
	/**
	 * 把扁平的页面列表构建成树结构。
	 *
	 * @param WP_Post[] $pages 页面列表。
	 * @return array<int,array{page:WP_Post,children:array}>
	 */
	function nimbus_build_page_tree( $pages ) {
		$by_id = array();

		foreach ( $pages as $page ) {
			$by_id[ (int) $page->ID ] = array(
				'page'     => $page,
				'children' => array(),
			);
		}

		$roots = array();

		foreach ( $by_id as $id => $node ) {
			$parent_id = (int) get_post_field( 'post_parent', $id );

			if ( $parent_id > 0 && isset( $by_id[ $parent_id ] ) ) {
				$by_id[ $parent_id ]['children'][] = &$by_id[ $id ];
				continue;
			}

			$roots[] = &$by_id[ $id ];
		}

		return $roots;
	}
}

if ( ! function_exists( 'nimbus_render_page_toc' ) ) {
	/**
	 * 渲染左侧的页面目录树。
	 *
	 * 每一项带 data-slug（短代码内容 / 页面别名），
	 * 客户端 JS 用它做展开状态与当前项高亮。
	 *
	 * @param array $nodes  树节点。
	 * @param int   $depth  当前深度。
	 * @return void
	 */
	function nimbus_render_page_toc( $nodes, $depth = 0 ) {
		if ( empty( $nodes ) ) {
			return;
		}

		$is_sub = $depth > 0;

		printf( '<ul class="%s">', $is_sub ? 'nb-toc__sublist' : 'nb-toc__list' );

		foreach ( $nodes as $node ) {
			$page = $node['page'];

			$permalink = get_permalink( $page );
			$children  = $node['children'];

			printf( '<li class="nb-toc__item" data-id="%d">', (int) $page->ID );

			// 有子项时输出折叠按钮。
			if ( ! empty( $children ) ) {
				printf(
					'<button type="button" class="nb-toc__toggle" data-nb-toggle aria-label="%1$s" aria-expanded="false"><svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M5.3 2.7 10.6 8l-5.3 5.3-1-1L8.6 8 4.3 3.7l1-1Z"/></svg></button>'
					,
					esc_attr__( '展开条目', 'nimbus-docs' )
				);
			}

			$link_class = 'nb-toc__link';

			if ( $depth >= 1 ) {
				$link_class .= ' nb-toc__link--h3';
			}

			printf(
				'<a class="%1$s" href="%2$s" data-nb-toc-link>%3$s</a>',
				esc_attr( $link_class ),
				esc_url( (string) $permalink ),
				esc_html( get_the_title( $page ) )
			);

			if ( ! empty( $children ) ) {
				nimbus_render_page_toc( $children, $depth + 1 );
			}

			echo '</li>';
		}

		echo '</ul>';
	}
}

if ( ! function_exists( 'nimbus_sidebar_toc' ) ) {
	/**
	 * 输出完整的左侧目录区。
	 *
	 * @return void
	 */
	function nimbus_sidebar_toc() {
		$pages = nimbus_get_doc_pages();

		if ( empty( $pages ) ) {
			return;
		}

		$tree = nimbus_build_page_tree( $pages );

		?>
		<nav class="nb-toc" data-nb-toc data-collapsed="false" aria-labelledby="nb-toc-heading">
			<h2 class="nb-toc__heading" id="nb-toc-heading">
				<?php esc_html_e( '文档目录', 'nimbus-docs' ); ?>
				<button type="button" class="nb-toc__collapse" data-nb-collapse>
					<svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" focusable="false"><path d="M3.3 5.3 8 10l4.7-4.7 1 1L8 12 2.3 6.3l1-1Z"/></svg>
					<span><?php esc_html_e( '收起', 'nimbus-docs' ); ?></span>
				</button>
			</h2>

			<?php nimbus_render_page_toc( $tree ); ?>
		</nav>
		<?php
	}
}

if ( ! function_exists( 'nimbus_sidebar' ) ) {
	/**
	 * 输出左侧栏：目录树 + Widget。
	 *
	 * @return void
	 */
	function nimbus_sidebar() {
		?>
		<aside id="nb-sidebar" class="nb-sidebar" data-nb-sidebar aria-label="<?php esc_attr_e( '文档目录', 'nimbus-docs' ); ?>">
			<?php
			nimbus_sidebar_toc();

			if ( is_active_sidebar( 'sidebar-1' ) ) {
				echo '<div class="nb-sidebar__widgets">';
				dynamic_sidebar( 'sidebar-1' );
				echo '</div>';
			}
			?>
		</aside>
		<?php
	}
}

if ( ! function_exists( 'nimbus_filter_code_blocks' ) ) {
	/**
	 * 为正文中的 pre 代码块包裹复制按钮容器。
	 *
	 * 只处理 <pre> 且没有语言禁用标记的块；
	 * 已在链接或手动容器中的块不重复处理。
	 *
	 * @param string $content 文章内容。
	 * @return string
	 */
	function nimbus_filter_code_blocks( $content ) {
		$pattern = '#<pre\b[^>]*>.*?</pre>#is';

		$result = preg_replace_callback(
			$pattern,
			'nimbus_wrap_code_block',
			$content
		);

		return ( null === $result ) ? $content : $result;
	}
}

if ( ! function_exists( 'nimbus_wrap_code_block' ) ) {
	/**
	 * 代码块包装回调。
	 *
	 * @param array $matches 正则匹配。
	 * @return string
	 */
	function nimbus_wrap_code_block( $matches ) {
		$block = $matches[0];

		// 作者显式关闭时不注入按钮。
		if ( false !== stripos( $block, 'no-copy' ) || false !== stripos( $block, 'language-none' ) ) {
			return $block;
		}

		// 已经包裹过。
		if ( false !== strpos( $block, 'nb-code__copy' ) ) {
			return $block;
		}

		$button = sprintf(
			'<button type="button" class="nb-code__copy" data-nb-copy>%1$s<span class="nb-code__copy-text">%2$s</span><span class="nb-screen-reader-text">%3$s</span></button>',
			nimbus_inline_icon( 'copy' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定 SVG 表。
			esc_html__( '复制', 'nimbus-docs' ),
			esc_html__( '复制代码到剪贴板', 'nimbus-docs' )
		);

		return '<div class="nb-code">' . $button . $block . '</div>';
	}
}

if ( ! function_exists( 'nimbus_add_heading_anchors' ) ) {
	/**
	 * 为正文标题补充锚点链接。
	 *
	 * 只在单篇文档页执行，且跳过代码块内的伪标题。
	 *
	 * @param string $content 文章内容。
	 * @return string
	 */
	function nimbus_add_heading_anchors( $content ) {
		if ( is_admin() || ! is_singular( array( 'post', 'page' ) ) ) {
			return $content;
		}

		if ( ! nimbus_get_option( 'heading_anchors' ) ) {
			return $content;
		}

		$pattern = '#<h([23])([^>]*)>(.*?)</h\1>#is';

		$result = preg_replace_callback(
			$pattern,
			'nimbus_insert_heading_anchor',
			$content
		);

		return ( null === $result ) ? $content : $result;
	}
}
add_filter( 'the_content', 'nimbus_add_heading_anchors', 30 );

if ( ! function_exists( 'nimbus_insert_heading_anchor' ) ) {
	/**
	 * 标题锚点注入回调。
	 *
	 * @param array $matches 正则匹配：1=级别，2=属性，3=内容。
	 * @return string
	 */
	function nimbus_insert_heading_anchor( $matches ) {
		$level      = $matches[1];
		$attributes = $matches[2];
		$inner      = $matches[3];

		// 已含锚点则跳过。
		if ( false !== strpos( $inner, 'nb-heading-anchor' ) || false !== strpos( $inner, 'nb-code' ) ) {
			return $matches[0];
		}

		// 已有 id 的标题复用，没有的不生成（交给 JS 处理）。
		$has_id = (bool) preg_match( '/\bid\s*=/i', $attributes );

		if ( ! $has_id ) {
			return $matches[0];
		}

		if ( ! preg_match( '/\bid\s*=\s*["\']([^"\']+)["\']/i', $attributes, $id_match ) ) {
			return $matches[0];
		}

		$anchor = sprintf(
			'<a class="nb-heading-anchor" href="#%1$s" aria-hidden="true" tabindex="-1">#</a>',
			esc_attr( $id_match[1] )
		);

		return '<h' . $level . $attributes . '>' . $inner . $anchor . '</h' . $level . '>';
	}
}

if ( ! function_exists( 'nimbus_inline_icon' ) ) {
	/**
	 * 内联SVG 图标表。
	 *
	 * @param string $name 图标名。
	 * @return string
	 */
	function nimbus_inline_icon( $name ) {
		$icons = array(
			'copy'      => '<path d="M5.6 1.6h6.1c.6 0 1 .4 1 1v.9h-1.3V2.9H5.6v7.3H4.3V2.6c0-.6.4-1 1-1h.3Zm-1.6 2.6h6.1c.6 0 1 .4 1 1v7.3c0 .6-.4 1-1 1H4c-.6 0-1-.4-1-1V5.2c0-.6.4-1 1-1Zm.3 1.3v6.7h5.5V5.5H4.3Z"/>',
			'check'     => '<path d="M13.3 4.1 6.4 11l-3.7-3.7 1-1 2.7 2.7 5.9-5.9 1 1Z"/>',
			'thumb-up'  => '<path d="M5.4 6.8 8.1 2c.3-.5 1.1-.3 1.1.3v3.1h3.4c.7 0 1.2.6 1.1 1.3l-.7 6.1c-.1.6-.6 1.1-1.2 1.1H5.4V6.8ZM1.6 6.8h2.5v7.1H1.6V6.8Z"/>',
			'thumb-down' => '<path d="M5.4 9.2 8.1 14c.3.5 1.1.3 1.1-.3v-3.1h3.4c.7 0 1.2-.6 1.1-1.3l-.7-6.1c-.1-.6-.6-1.1-1.2-1.1H5.4v6.8ZM1.6 9.2h2.5V2.1H1.6v7.1Z"/>',
			'search'    => '<path d="M6.8 1.6a5.2 5.2 0 0 1 4.08 8.4l3.6 3.6-1.06 1.06-3.6-3.6A5.2 5.2 0 1 1 6.8 1.6Zm0 1.46a3.74 3.74 0 1 0 0 7.48 3.74 3.74 0 0 0 0-7.48Z"/>',
			'menu'      => '<path d="M2 4h12v1.3H2V4Zm0 3.3h12v1.3H2V7.3ZM2 10.6h12v1.3H2v-1.3Z"/>',
			'close'     => '<path d="M12.1 3.5 8.5 7.1l-3.6-3.6-1 1 3.6 3.6-3.6 3.6 1 1 3.6-3.6 3.6 3.6 1-1L9.5 8.1l3.6-3.6-1-1Z"/>',
			'external'  => '<path d="M9 2h5v5h-1.3V4.56l-5.1 5.1-.93-.93 5.1-5.1H9V2ZM2.6 3.4h3.5v1.3H3.9v7.4h7.4V9.6h1.3v3.5a1.3 1.3 0 0 1-1.3 1.3H3.9a1.3 1.3 0 0 1-1.3-1.3V4.7c0-.7.6-1.3 1.3-1.3Z"/>',
		);

		$name = (string) $name;

		if ( ! isset( $icons[ $name ] ) ) {
			$name = 'search';
		}

		return sprintf(
			'<svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" focusable="false">%s</svg>',
			$icons[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定字面量表。
		);
	}
}
