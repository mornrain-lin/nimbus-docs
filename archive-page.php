<?php
/**
 * 页面归档模板：列出所有文档页面。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();
?>

<header class="nb-page-header">
	<h1 class="nb-page-header__title">
		<?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?>
	</h1>

	<?php
	$nb_pages_total = wp_count_posts( 'page' );
	$nb_pages_total = isset( $nb_pages_total->publish ) ? (int) $nb_pages_total->publish : 0;
	?>
	<p class="nb-page-header__description">
		<?php
		printf(
			/* translators: %s：页面数量。 */
			esc_html__( '共 %s 篇文档。', 'nimbus-docs' ),
			esc_html( number_format_i18n( $nb_pages_total ) )
		);
		?>
	</p>
</header>

<?php if ( have_posts() ) : ?>

	<?php nimbus_page_list( 'page' ); ?>

	<?php nimbus_pagination(); ?>

<?php else : ?>

	<div class="nb-no-results">
		<h2><?php esc_html_e( '还没有文档', 'nimbus-docs' ); ?></h2>
		<p><?php esc_html_e( '发布第一篇页面后，它会自动出现在左侧目录中。', 'nimbus-docs' ); ?></p>
	</div>

<?php endif; ?>

<?php
get_footer();
