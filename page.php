<?php
/**
 * 单页面模板（文档布局）。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();
?>

<?php
while ( have_posts() ) :
	the_post();

	$nb_ancestors = array_reverse( (array) get_post_ancestors( (int) get_the_ID() ) );
	?>

	<?php if ( nimbus_get_option( 'breadcrumb_enabled' ) && ! empty( $nb_ancestors ) ) : ?>
		<?php nimbus_breadcrumb(); ?>
	<?php endif; ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'nb-doc' ); ?>>

		<header class="nb-page-header">
			<?php
			$nb_parent_title = '';

			if ( ! empty( $nb_ancestors ) ) {
				$nb_parent_title = get_the_title( (int) end( $nb_ancestors ) );
			}

			if ( '' !== $nb_parent_title ) :
				?>
				<span class="nb-page-header__eyebrow"><?php echo esc_html( $nb_parent_title ); ?></span>
			<?php endif; ?>

			<h1 class="nb-page-header__title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="nb-page-header__description"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<?php
			$nb_updated = get_the_modified_time( 'U' );

			if ( $nb_updated ) :
				?>
				<ul class="nb-page-header__meta">
					<li>
						<?php
						$nb_author_id = (int) get_the_author_meta( 'ID' );

						if ( $nb_author_id && 'page' !== get_post_type() ) {
							printf(
								'<a href="%1$s" rel="author">%2$s</a>',
								esc_url( (string) get_author_posts_url( $nb_author_id ) ),
								esc_html( get_the_author() )
							);
						} else {
							esc_html_e( '文档', 'nimbus-docs' );
						}
						?>
					</li>
					<li>
						<time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>">
							<?php
							printf(
								/* translators: %s：最后更新日期。 */
								esc_html__( '更新于 %s', 'nimbus-docs' ),
								esc_html( get_the_modified_date() )
							);
							?>
						</time>
					</li>
					<?php if ( comments_open() || get_comments_number() ) : ?>
						<li>
							<span>
								<?php
								$nb_comments = (int) get_comments_number();

								printf(
									/* translators: %s：评论数。 */
									esc_html( _n( '%s 条评论', '%s 条评论', $nb_comments, 'nimbus-docs' ) ),
									esc_html( number_format_i18n( $nb_comments ) )
								);
								?>
							</span>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</header>

		<div class="nb-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before'      => '<nav class="nb-pagination">' . esc_html__( '页面：', 'nimbus-docs' ),
					'after'       => '</nav>',
					'link_before' => '<span class="page-numbers">',
					'link_after'  => '</span>',
				)
			);
			?>
		</div>

		<?php nimbus_render_feedback(); ?>

		<?php if ( nimbus_get_option( 'pager_enabled' ) ) : ?>
			<?php nimbus_pager(); ?>
		<?php endif; ?>

	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
	?>

	<?php
endwhile;
?>

<?php
get_footer();
