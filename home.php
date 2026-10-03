<?php
/**
 * 前台文章列表模板（home.php）。
 *
 * WordPress 的模板层级：当站点首页被指定为「文章」时，
 * 会依次查找 front-page.php → home.php → index.php。
 * 本文件与index.php 的文章列表结构保持一致，
 * 存在的意义是让子主题可以单独覆盖文章列表而不影响首页。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();
?>

<header class="nb-page-header">
	<h1 class="nb-page-header__title">
		<?php
		$nb_blog_page = (int) get_option( 'page_for_posts' );

		echo esc_html( $nb_blog_page ? (string) get_the_title( $nb_blog_page ) : esc_html__( '最新文章', 'nimbus-docs' ) );
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