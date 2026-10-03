<?php
/**
 * 主模板文件（fallback template）。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();
?>

<header class="nb-page-header">
	<h1 class="nb-page-header__title">
		<?php
		if ( is_home() && ! is_front_page() ) {
			$nb_blog_page = (int) get_option( 'page_for_posts' );

			echo esc_html( $nb_blog_page ? (string) get_the_title( $nb_blog_page ) : esc_html__( '最新文章', 'nimbus-docs' ) );
		} else {
			echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
		}
		?>
	</h1>

	<?php
	$nb_description = get_the_archive_description();

	if ( $nb_description ) {
		echo '<p class="nb-page-header__description">' . wp_kses_post( wpautop( $nb_description ) ) . '</p>';
	}
	?>
</header>

<?php if ( have_posts() ) : ?>

	<?php nimbus_page_list( 'post' ); ?>

	<?php nimbus_pagination(); ?>

<?php else : ?>

	<div class="nb-no-results">
		<h2><?php esc_html_e( '暂时没有内容', 'nimbus-docs' ); ?></h2>
		<p><?php esc_html_e( '换个关键词，或从左侧目录选择一篇文档。', 'nimbus-docs' ); ?></p>
		<?php get_search_form(); ?>
	</div>

<?php endif; ?>

<?php
get_footer();
