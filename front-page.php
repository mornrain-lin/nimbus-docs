<?php
/**
 * 首页模板（front-page.php）。
 *
 * 文档主题的首页应当是「文档总览」而不是普通文章流：
 * - 指定了静态首页：先输出该页面内容，再列出文档页面树。
 * - 首页显示「文章」：直接输出文章列表（与home.php 一致）。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

get_header();

$nb_is_static_front = is_front_page() && is_page();
?>

<?php if ( $nb_is_static_front ) : ?>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'nb-doc' ); ?>>
			<div class="nb-content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>

	<?php
	$nb_pages = nimbus_get_doc_pages();

	if ( ! empty( $nb_pages ) ) :
		?>
		<header class="nb-page-header">
			<h2 class="nb-page-header__title"><?php esc_html_e( '全部文档', 'nimbus-docs' ); ?></h2>
			<p class="nb-page-header__description">
				<?php
				printf(
					/* translators: %s：文档页面数量。 */
					esc_html__( '共 %s 篇文档。', 'nimbus-docs' ),
					esc_html( number_format_i18n( count( $nb_pages ) ) )
				);
				?>
			</p>
		</header>

		<?php nimbus_page_list( 'page', $nb_pages ); ?>
		<?php
	else :
		?>
		<div class="nb-no-results">
			<h2><?php esc_html_e( '还没有文档', 'nimbus-docs' ); ?></h2>
			<p><?php esc_html_e( '发布第一篇页面后，它会自动出现在左侧目录中。', 'nimbus-docs' ); ?></p>
		</div>
		<?php
	endif;

elseif ( have_posts() ) :
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