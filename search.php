<?php
/**
 * 搜索结果模板（带关键词高亮）。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();

global $wp_query;
$nb_found = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
?>

<header class="nb-page-header">
	<h1 class="nb-page-header__title">
		<?php
		printf(
			/* translators: %s：搜索关键词。 */
			esc_html__( '搜索：%s', 'nimbus-docs' ),
			esc_html( get_search_query() )
		);
		?>
	</h1>

	<?php if ( have_posts() ) : ?>
		<p class="nb-page-header__description">
			<?php
			printf(
				/* translators: %s：结果数量。 */
				esc_html( _n( '共找到 %s 条结果。', '共找到 %s 条结果。', $nb_found, 'nimbus-docs' ) ),
				esc_html( number_format_i18n( $nb_found ) )
			);
			?>
		</p>
	<?php endif; ?>
</header>

<?php if ( have_posts() ) : ?>

	<ul class="nb-search-results">
		<?php
		while ( have_posts() ) :
			the_post();

			$nb_excerpt = nimbus_get_excerpt();
			?>
			<li class="nb-search-item">
				<h2 class="nb-search-item__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>

				<span class="nb-search-item__url"><?php echo esc_html( str_replace( home_url(), '', (string) get_permalink() ) ); ?></span>

				<?php if ( '' !== $nb_excerpt ) : ?>
					<p class="nb-search-item__excerpt" data-nb-highlight><?php echo esc_html( $nb_excerpt ); ?></p>
				<?php endif; ?>
			</li>
			<?php
		endwhile;
		?>
	</ul>

	<?php nimbus_pagination(); ?>

<?php else : ?>

	<div class="nb-no-results">
		<h2><?php esc_html_e( '没有找到相关文档', 'nimbus-docs' ); ?></h2>
		<p><?php esc_html_e( '试试更短的关键词，或从左侧目录直接浏览。', 'nimbus-docs' ); ?></p>
		<?php get_search_form(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
