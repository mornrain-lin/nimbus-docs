<?php
/**
 * Nimbus Docs 文档反馈模块。
 *
 * 每篇文档底部有「这篇文档对你有帮助吗？」的两个按钮。
 * 点击后写入 postmeta：
 * - _nimbus_feedback_up   有帮助的次数
 * - _nimbus_feedback_down 没帮助的次数
 * - _nimbus_feedback_log  最近一次反馈的时间戳（用于防重复提交与展示）
 *
 * 前端用 admin-ajax.php 提交，Nonce 由 wp_create_nonce( 'nimbus_feedback' ) 生成。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nimbus_feedback_counts' ) ) {
	/**
	 * 读取一篇文章的反馈统计。
	 *
	 * @param int $post_id 文章 ID。
	 * @return array{up:int,down:int,total:int}
	 */
	function nimbus_feedback_counts( $post_id ) {
		$post_id = (int) $post_id;

		$up   = (int) get_post_meta( $post_id, '_nimbus_feedback_up', true );
		$down = (int) get_post_meta( $post_id, '_nimbus_feedback_down', true );

		return array(
			'up'    => max( 0, $up ),
			'down'  => max( 0, $down ),
			'total' => max( 0, $up ) + max( 0, $down ),
		);
	}
}

if ( ! function_exists( 'nimbus_render_feedback' ) ) {
	/**
	 * 渲染反馈区域。
	 *
	 * @return void
	 */
	function nimbus_render_feedback() {
		if ( ! nimbus_get_option( 'feedback_enabled' ) ) {
			return;
		}

		if ( ! is_singular( array( 'post', 'page' ) ) ) {
			return;
		}

		$post_id = (int) get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$counts = nimbus_feedback_counts( $post_id );
		?>
		<div class="nb-feedback" data-nb-feedback data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<p class="nb-feedback__question">
				<?php esc_html_e( '这篇文档对你有帮助吗？', 'nimbus-docs' ); ?>
			</p>

			<div class="nb-feedback__buttons">
				<button type="button" class="nb-feedback__btn nb-feedback__btn--up" data-nb-feedback-value="up">
					<?php echo nimbus_inline_icon( 'thumb-up' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定 SVG 表。 ?>
					<span><?php esc_html_e( '有帮助', 'nimbus-docs' ); ?></span>
				</button>

				<button type="button" class="nb-feedback__btn nb-feedback__btn--down" data-nb-feedback-value="down">
					<?php echo nimbus_inline_icon( 'thumb-down' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定 SVG 表。 ?>
					<span><?php esc_html_e( '没帮助', 'nimbus-docs' ); ?></span>
				</button>
			</div>

			<p class="nb-feedback__result" data-nb-feedback-result role="status" aria-live="polite">
				<?php
				if ( $counts['total'] > 0 ) {
					printf(
						/* translators: %s：总反馈数。 */
						esc_html__( '已有 %s 位读者反馈。', 'nimbus-docs' ),
						esc_html( number_format_i18n( $counts['total'] ) )
					);
				}
				?>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'nimbus_ajax_submit_feedback' ) ) {
	/**
	 * AJAX 反馈处理器。
	 *
	 * @return void
	 */
	function nimbus_ajax_submit_feedback() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'nimbus_feedback' ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( '请求已失效，请刷新页面后重试。', 'nimbus-docs' ) ),
				403
			);
		}

		$post_id = isset( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;

		if ( ! $post_id ) {
			wp_send_json_error(
				array( 'message' => esc_html__( '缺少文章 ID。', 'nimbus-docs' ) ),
				400
			);
		}

		$value = isset( $_POST['value'] ) ? sanitize_key( wp_unslash( $_POST['value'] ) ) : '';

		if ( ! in_array( $value, array( 'up', 'down' ), true ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( '反馈值不合法。', 'nimbus-docs' ) ),
				400
			);
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			wp_send_json_error(
				array( 'message' => esc_html__( '文章不存在。', 'nimbus-docs' ) ),
				404
			);
		}

		// 同一 IP 对同一篇文章 1 小时内只计一次，避免刷量。
		$ip_hash   = nimbus_hash_ip();
		$transient = 'nimbus_fb_' . md5( $post_id . '|' . $ip_hash );

		if ( get_transient( $transient ) ) {
			$counts = nimbus_feedback_counts( $post_id );

			wp_send_json_success(
				array(
					'counts'  => $counts,
					'message' => esc_html__( '已经记录过你的反馈了，谢谢。', 'nimbus-docs' ),
					'limited' => true,
				)
			);
		}

		set_transient( $transient, 1, HOUR_IN_SECONDS );

		$meta_key = '_nimbus_feedback_' . $value;

		$current = (int) get_post_meta( $post_id, $meta_key, true );

		update_post_meta( $post_id, $meta_key, $current + 1 );
		update_post_meta( $post_id, '_nimbus_feedback_log', current_time( 'mysql' ) );

		$counts = nimbus_feedback_counts( $post_id );

		/**
		 * 反馈提交成功后的钩子。
		 *
		 * @param int    $post_id 文章 ID。
		 * @param string $value   up / down。
		 * @param array  $counts  统计结果。
		 */
		do_action( 'nimbus_feedback_submitted', $post_id, $value, $counts );

		wp_send_json_success(
			array(
				'counts'  => $counts,
				'message' => esc_html__( '感谢反馈！', 'nimbus-docs' ),
				'limited' => false,
			)
		);
	}
}
add_action( 'wp_ajax_nimbus_submit_feedback', 'nimbus_ajax_submit_feedback' );
add_action( 'wp_ajax_nopriv_nimbus_submit_feedback', 'nimbus_ajax_submit_feedback' );

if ( ! function_exists( 'nimbus_hash_ip' ) ) {
	/**
	 * 生成请求 IP 的哈希（不存明文）。
	 *
	 * @return string
	 */
	function nimbus_hash_ip() {
		$ip = '';

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		if ( '' === $ip ) {
			return '';
		}

		return substr( md5( $ip . wp_salt( 'nonce' ) ), 0, 32 );
	}
}

if ( ! function_exists( 'nimbus_add_feedback_column' ) ) {
	/**
	 * 在文章列表中显示反馈数据。
	 *
	 * @param array $columns 默认列。
	 * @return array
	 */
	function nimbus_add_feedback_column( $columns ) {
		if ( ! nimbus_get_option( 'feedback_enabled' ) ) {
			return $columns;
		}

		$new = array();

		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['nimbus_feedback'] = esc_html__( '文档反馈', 'nimbus-docs' );
			}
		}

		return $new;
	}
}
add_filter( 'manage_post_posts_columns', 'nimbus_add_feedback_column' );
add_filter( 'manage_page_posts_columns', 'nimbus_add_feedback_column' );

if ( ! function_exists( 'nimbus_render_feedback_column' ) ) {
	/**
	 * 反馈列的内容。
	 *
	 * @param string $column  列名。
	 * @param int    $post_id 文章 ID。
	 * @return void
	 */
	function nimbus_render_feedback_column( $column, $post_id ) {
		if ( 'nimbus_feedback' !== $column ) {
			return;
		}

		$counts = nimbus_feedback_counts( (int) $post_id );

		if ( 0 === $counts['total'] ) {
			echo '<span aria-hidden="true">—</span>';
			return;
		}

		$ratio = $counts['total'] > 0 ? round( ( $counts['up'] / $counts['total'] ) * 100 ) : 0;

		printf(
			'<span title="%1$s">👍 %2$s · 👎 %3$s (%4$s%%)</span>',
			esc_attr__( '有帮助 / 没帮助', 'nimbus-docs' ),
			esc_html( number_format_i18n( $counts['up'] ) ),
			esc_html( number_format_i18n( $counts['down'] ) ),
			esc_html( number_format_i18n( $ratio ) )
		);
	}
}
add_action( 'manage_post_posts_custom_column', 'nimbus_render_feedback_column', 10, 2 );
add_action( 'manage_page_posts_custom_column', 'nimbus_render_feedback_column', 10, 2 );
