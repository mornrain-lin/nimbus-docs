<?php
/**
 * Nimbus Docs 站点页脚模板。
 *
 * @package NimbusDocs
 * @since   1.0.0
 */

?>
			</div><!-- .nb-main__inner -->
		</main><!-- #nb-content -->

	</div><!-- .nb-docs-layout -->

	<footer id="colophon" class="nb-footer">
		<div class="nb-container">
			<?php
			if ( nimbus_get_option( 'footer_widgets' ) ) {
				nimbus_footer_widgets();
			}
			?>

			<div class="nb-footer__inner">
				<p>
					<?php
					printf(
						/* translators: 1：年份，2：站点名称。 */
						esc_html__( '© %1$s %2$s', 'nimbus-docs' ),
						esc_html( wp_date( 'Y' ) ),
						esc_html( get_bloginfo( 'name', 'display' ) )
					);
					?>
				</p>

				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( '页脚导航', 'nimbus-docs' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => 'nb-footer-menu',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
