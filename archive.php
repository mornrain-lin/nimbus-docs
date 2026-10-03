<?php
/**
 * 归档模板：分类、标签、日期、作者。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();
?>

<header class="nb-page-header">
	<h1 class="nb-page-header__title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>

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
		<h2><?php esc_html_e( '该归档暂无内容', 'nimbus-docs' ); ?></h2>
		<p><?php esc_html_e( '换个分类，或使用搜索查找文档。', 'nimbus-docs' ); ?></p>
	</div>

<?php endif; ?>

<?php
get_footer();
