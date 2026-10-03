<?php
/**
 * Nimbus Docs 模板标签。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nimbus_header_search' ) ) {
	/**
	 * 输出头部搜索框。
	 *
	 * @return void
	 */
	function nimbus_header_search() {
		$field_id = 'nb-header-search-' . wp_rand( 1000, 9999 );
		?>
		<div class="nb-search">
			<label class="nb-screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>">
				<?php esc_html_e( '搜索文档', 'nimbus-docs' ); ?>
			</label>
			<?php echo nimbus_inline_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定 SVG 表。 ?>
			<input
				type="search"
				id="<?php echo esc_attr( $field_id ); ?>"
				class="nb-search__field"
				data-nb-search
				placeholder="<?php esc_attr_e( '搜索文档…', 'nimbus-docs' ); ?>"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				autocomplete="off"
			>
			<kbd class="nb-search__kbd">/</kbd>
		</div>
		<?php
	}
}

if ( ! function_exists( 'nimbus_sidebar_toggle_button' ) ) {
	/**
	 * 输出移动端侧栏开关。
	 *
	 * @return void
	 */
	function nimbus_sidebar_toggle_button() {
		?>
		<button
			type="button"
			class="nb-icon-btn nb-sidebar-toggle"
			data-nb-sidebar-toggle
			aria-expanded="false"
			aria-controls="nb-sidebar"
			aria-label="<?php esc_attr_e( '切换目录侧栏', 'nimbus-docs' ); ?>"
		>
			<?php echo nimbus_inline_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定 SVG 表。 ?>
		</button>
		<?php
	}
}

if ( ! function_exists( 'nimbus_sidebar_backdrop' ) ) {
	/**
	 * 输出移动端侧栏遮罩。
	 *
	 * @return void
	 */
	function nimbus_sidebar_backdrop() {
		echo '<div class="nb-sidebar-backdrop" data-nb-backdrop hidden></div>';
	}
}
add_action( 'wp_body_open', 'nimbus_sidebar_backdrop', 5 );

if ( ! function_exists( 'nimbus_primary_nav' ) ) {
	/**
	 * 输出主导航。
	 *
	 * @return void
	 */
	function nimbus_primary_nav() {
		if ( ! has_nav_menu( 'primary' ) ) {
			return;
		}

		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nb-primary-menu',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);
	}
}

if ( ! function_exists( 'nimbus_pager' ) ) {
	/**
	 * 输出上下页导航。
	 *
	 * 文档站需要「同章节内」导航，因此除了常规上一篇/下一篇，
	 * 还会优先寻找同父页面的相邻兄弟页面。
	 *
	 * @return void
	 */
	function nimbus_pager() {
		if ( ! is_singular( array( 'post', 'page' ) ) ) {
			return;
		}

		$post_id = (int) get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$parent_id = (int) get_post_field( 'post_parent', $post_id );
		$previous  = null;
		$next      = null;

		// 有父页面时，先在兄弟页面中找。
		if ( $parent_id > 0 ) {
			$siblings = get_posts(
				array(
					'post_type'      => 'page',
					'post_parent'    => $parent_id,
					'post_status'    => 'publish',
					'posts_per_page' => 50,
					'orderby'        => array(
						'menu_order' => 'ASC',
						'title'      => 'ASC',
					),
					'no_found_rows'  => true,
				)
			);

			$sibling_ids = wp_list_pluck( $siblings, 'ID' );
			$position    = array_search( $post_id, array_map( 'intval', $sibling_ids ), true );

			if ( false !== $position ) {
				$position = (int) $position;

				if ( $position > 0 ) {
					$previous = get_post( (int) $sibling_ids[ $position - 1 ] );
				}

				if ( isset( $sibling_ids[ $position + 1 ] ) ) {
					$next = get_post( (int) $sibling_ids[ $position + 1 ] );
				}
			}
		}

		// 兄弟里找不到时，回落到全站上一篇 / 下一篇。
		if ( ! $previous ) {
			$raw_previous = get_previous_post();

			if ( $raw_previous && (int) $raw_previous->post_parent === $parent_id ) {
				$previous = $raw_previous;
			}
		}

		if ( ! $next ) {
			$raw_next = get_next_post();

			if ( $raw_next && (int) $raw_next->post_parent === $parent_id ) {
				$next = $raw_next;
			}
		}

		if ( ! $previous && ! $next ) {
			return;
		}

		echo '<nav class="nb-pager" aria-label="' . esc_attr__( '文档导航', 'nimbus-docs' ) . '">';

		if ( $previous instanceof WP_Post ) {
			echo '<a class="nb-pager__link nb-pager__link--prev" href="' . esc_url( (string) get_permalink( $previous ) ) . '" rel="prev">';
			printf( '<span class="nb-pager__label">%s</span>', esc_html__( '上一节', 'nimbus-docs' ) );
			printf( '<span class="nb-pager__title">%s</span>', esc_html( get_the_title( $previous ) ) );
			echo '</a>';
		}

		if ( $next instanceof WP_Post ) {
			echo '<a class="nb-pager__link nb-pager__link--next" href="' . esc_url( (string) get_permalink( $next ) ) . '" rel="next">';
			printf( '<span class="nb-pager__label">%s</span>', esc_html__( '下一节', 'nimbus-docs' ) );
			printf( '<span class="nb-pager__title">%s</span>', esc_html( get_the_title( $next ) ) );
			echo '</a>';
		}

		echo '</nav>';
	}
}

if ( ! function_exists( 'nimbus_breadcrumb' ) ) {
	/**
	 * 输出面包屑（层级页面的路径）。
	 *
	 * @return void
	 */
	function nimbus_breadcrumb() {
		if ( is_front_page() || ! is_singular() ) {
			return;
		}

		$post_id   = (int) get_the_ID();
		$ancestors = array_reverse( (array) get_post_ancestors( $post_id ) );

		if ( empty( $ancestors ) ) {
			return;
		}

		?>
		<nav class="nb-breadcrumb" aria-label="<?php esc_attr_e( '文档路径', 'nimbus-docs' ); ?>">
			<ol>
				<li>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '首页', 'nimbus-docs' ); ?></a>
				</li>
				<?php foreach ( $ancestors as $ancestor_id ) : ?>
					<li>
						<a href="<?php echo esc_url( (string) get_permalink( $ancestor_id ) ); ?>">
							<?php echo esc_html( get_the_title( $ancestor_id ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li>
					<span aria-current="page"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
				</li>
			</ol>
		</nav>
		<?php
	}
}

if ( ! function_exists( 'nimbus_pagination' ) ) {
	/**
	 * 输出分页标记。
	 *
	 * @return void
	 */
	function nimbus_pagination() {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query || $wp_query->max_num_pages < 2 ) {
			return;
		}

		$links = paginate_links(
			array(
				'total'     => $wp_query->max_num_pages,
				'current'   => max( 1, (int) get_query_var( 'paged' ) ),
				'type'      => 'array',
				'mid_size'  => 1,
				'prev_text' => esc_html__( '上一页', 'nimbus-docs' ),
				'next_text' => esc_html__( '下一页', 'nimbus-docs' ),
			)
		);

		if ( ! $links ) {
			return;
		}

		echo '<nav class="nb-pagination" aria-label="' . esc_attr__( '分页导航', 'nimbus-docs' ) . '"><div class="nav-links">';

		foreach ( $links as $link ) {
			echo wp_kses_post( $link );
		}

		echo '</div></nav>';
	}
}

if ( ! function_exists( 'nimbus_page_list' ) ) {
	/**
	 * 输出文章 / 页面列表（归档、搜索与首页共用）。
	 *
	 * 传入 $posts 时直接渲染该数组（适用于首页这类主查询已被消费的场景）；
	 * 不传则渲染当前主查询。
	 *
	 * @param string           $post_type 文章类型。
	 * @param WP_Post[]|null  $posts     可选的指定文章数组。
	 * @return void
	 */
	function nimbus_page_list( $post_type = 'post', $posts = null ) {
		$badge = ( 'page' === $post_type );

		if ( is_array( $posts ) ) {
			$items = $posts;
		} else {
			$items = array();

			while ( have_posts() ) {
				the_post();
				$items[] = get_post();
			}
		}

		if ( empty( $items ) ) {
			return;
		}

		?>
		<ul class="nb-page-list">
			<?php
			foreach ( $items as $nb_list_post ) :
				$nb_list_post = get_post( $nb_list_post );

				if ( ! $nb_list_post instanceof WP_Post ) {
					continue;
				}

				$permalink = get_permalink( $nb_list_post );

				if ( ! $permalink ) {
					continue;
				}
				?>
				<li class="nb-page-item">
					<div class="nb-page-item__body">
						<h2 class="nb-page-item__title">
							<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $nb_list_post ) ); ?></a>
						</h2>

						<?php
						$excerpt = nimbus_get_excerpt( $nb_list_post );

						if ( '' !== $excerpt ) {
							echo '<p class="nb-page-item__excerpt">' . esc_html( $excerpt ) . '</p>';
						}
						?>
					</div>

					<?php
					if ( $badge ) :
						$nb_post_type_object = get_post_type_object( $post_type );

						if ( $nb_post_type_object instanceof WP_Post_Type ) {
							printf(
								'<span class="nb-page-item__badge">%s</span>',
								esc_html( $nb_post_type_object->labels->singular_name )
							);
						}
					endif;
					?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php

		if ( ! is_array( $posts ) ) {
			wp_reset_postdata();
		}
	}
}

if ( ! function_exists( 'nimbus_get_excerpt' ) ) {
	/**
	 * 获取文章摘要纯文本。
	 *
	 * @param int|WP_Post|null $post 文章对象或 ID。
	 * @return string
	 */
	function nimbus_get_excerpt( $post = null ) {
		$post = get_post( $post );

		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$length = (int) nimbus_get_option( 'excerpt_length' );

		if ( $length < 10 ) {
			$length = 32;
		}

		if ( '' !== trim( (string) $post->post_excerpt ) ) {
			return wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ) );
		}

		$content = $post->post_password_required() ? '' : $post->post_content;
		$content = strip_shortcodes( $content );
		$content = excerpt_remove_blocks( $content );

		return wp_trim_words( wp_strip_all_tags( $content ), $length, '…' );
	}
}

if ( ! function_exists( 'nimbus_footer_widgets' ) ) {
	/**
	 * 输出页脚 Widget。
	 *
	 * @return void
	 */
	function nimbus_footer_widgets() {
		if ( ! is_active_sidebar( 'footer-1' ) && ! is_active_sidebar( 'footer-2' ) ) {
			return;
		}

		echo '<div class="nb-footer__widgets">';

		for ( $i = 1; $i <= 2; $i++ ) {
			$id = 'footer-' . $i;

			if ( ! is_active_sidebar( $id ) ) {
				continue;
			}

			echo '<div class="nb-footer__widgets__col">';
			dynamic_sidebar( $id );
			echo '</div>';
		}

		echo '</div>';
	}
}
