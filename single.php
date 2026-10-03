<?php
/**
 * 单篇文章模板（文档布局）。
 *
 * 文章与页面使用同一套文档布局：左侧目录、右侧正文、反馈与上下节导航。
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
	$nb_categories = get_the_category( (int) get_the_ID() );
	?>

	<?php if ( nimbus_get_option( 'breadcrumb_enabled' ) && ! empty( $nb_ancestors ) ) : ?>
		<?php nimbus_breadcrumb(); ?>
	<?php endif; ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'nb-doc' ); ?>>

		<header class="nb-page-header">
			<?php if ( ! empty( $nb_categories ) && ! is_wp_error( $nb_categories ) ) : ?>
				<span class="nb-page-header__eyebrow">
					<a href="<?php echo esc_url( (string) get_category_link( $nb_categories[0]->term_id ) ); ?>">
						<?php echo esc_html( $nb_categories[0]->name ); ?>
					</a>
				</span>
			<?php endif; ?>

			<h1 class="nb-page-header__title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="nb-page-header__description"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<ul class="nb-page-header__meta">
				<li>
					<a href="<?php echo esc_url( (string) get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
						<?php echo esc_html( get_the_author() ); ?>
					</a>
				</li>
				<li>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</li>
				<?php if ( get_the_modified_time( 'U' ) !== get_the_time( 'U' ) ) : ?>
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
				<?php endif; ?>
			</ul>
		</header>

		<div class="nb-content">
			<?php
			the_content(
				sprintf(
					/* translators: %s：文章标题。 */
					esc_html__( '继续阅读「%s」', 'nimbus-docs' ),
					esc_html( get_the_title() )
				)
			);

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

		<?php
		$nb_tags = get_the_tag_list( '', '' );

		if ( $nb_tags && ! is_wp_error( $nb_tags ) ) {
			echo '<ul class="nb-entry__tags">' . wp_kses_post( $nb_tags ) . '</ul>';
		}

		nimbus_render_feedback();

		if ( nimbus_get_option( 'pager_enabled' ) ) {
			nimbus_pager();
		}
		?>

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
