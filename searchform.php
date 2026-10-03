<?php
/**
 * 搜索表单模板。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

$nb_search_id = 'nb-search-' . wp_rand( 1000, 9999 );
?>

<form role="search" method="get" class="nb-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="nb-screen-reader-text" for="<?php echo esc_attr( $nb_search_id ); ?>">
		<?php esc_html_e( '搜索文档', 'nimbus-docs' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $nb_search_id ); ?>"
		class="nb-search__field"
		placeholder="<?php esc_attr_e( '搜索文档…', 'nimbus-docs' ); ?>"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		name="s"
	>
	<button type="submit" class="nb-button"><?php esc_html_e( '搜索', 'nimbus-docs' ); ?></button>
</form>
