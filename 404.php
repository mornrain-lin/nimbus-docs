<?php
/**
 * 404 未找到页面模板。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();
?>

<div class="nb-404">
	<p class="nb-404__code">404</p>
	<h1 class="nb-404__title"><?php esc_html_e( '找不到这个文档', 'nimbus-docs' ); ?></h1>
	<p class="nb-404__text"><?php esc_html_e( '地址可能有误，或者对应的文档已被移动。试试搜索，或从左侧目录重新进入。', 'nimbus-docs' ); ?></p>

	<div class="nb-404__actions">
		<button type="button" class="nb-button" data-nb-focus-search><?php esc_html_e( '搜索文档', 'nimbus-docs' ); ?></button>
		<a class="nb-button nb-button--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( '返回首页', 'nimbus-docs' ); ?>
		</a>
	</div>

	<?php get_search_form(); ?>
</div>

<?php
get_footer();
