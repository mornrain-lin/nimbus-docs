<?php
/**
 * Nimbus Docs 站点头部模板。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
} else {
	do_action( 'wp_body_open' );
}
?>

<a class="nb-skip-link" href="#nb-content"><?php esc_html_e( '跳到正文', 'nimbus-docs' ); ?></a>

<div id="page" class="nb-site">

	<header id="masthead" class="nb-header">
		<div class="nb-container">
			<div class="nb-header__inner">

				<?php nimbus_sidebar_toggle_button(); ?>

				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php endif; ?>

				<a class="nb-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="nb-brand__name"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></span>
					<span class="nb-brand__docs"><?php esc_html_e( 'Docs', 'nimbus-docs' ); ?></span>
				</a>

				<div class="nb-header__search">
					<?php nimbus_header_search(); ?>
				</div>

				<div class="nb-header__tools">
					<?php nimbus_primary_nav(); ?>
				</div>

			</div>
		</div>
	</header>

	<div class="nb-docs-layout">

		<?php nimbus_sidebar(); ?>

		<main id="nb-content" class="nb-main" tabindex="-1">
			<div class="nb-main__inner">
