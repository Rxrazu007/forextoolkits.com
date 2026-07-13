<?php
/**
 * Single entry template for CPTs (forecast, indicator, ea)
 * Same as Kadence's single-entry.php but without 'post' === get_post_type() checks
 */
namespace Kadence;
use function Kadence\kadence;
?>
<?php
if ( kadence()->show_feature_above() ) {
	get_template_part( 'template-parts/content/entry_thumbnail', get_post_type() );
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry content-bg single-entry' . ( kadence()->option( 'post_footer_area_boxed' ) ? ' post-footer-area-boxed' : '' ) ); ?>>
	<div class="entry-content-wrap">
		<?php do_action( 'kadence_single_before_inner_content' ); ?>
		<?php if ( kadence()->show_in_content_title() ) { get_template_part( 'template-parts/content/entry_header', get_post_type() ); } ?>
		<?php if ( kadence()->show_feature_below() ) { get_template_part( 'template-parts/content/entry_thumbnail', get_post_type() ); } ?>
		<?php get_template_part( 'template-parts/content/entry_content', get_post_type() ); ?>
		<?php if ( kadence()->option( 'post_tags' ) ) { get_template_part( 'template-parts/content/entry_footer', get_post_type() ); } ?>
		<?php do_action( 'kadence_single_after_inner_content' ); ?>
	</div>
</article>
<?php do_action( 'kadence_single_after_content' ); ?>
<?php if ( is_singular( get_post_type() ) ) : ?>
	<?php if ( kadence()->option( 'post_author_box' ) ) { get_template_part( 'template-parts/content/entry_author', get_post_type() ); } ?>
	<?php if ( get_post_type_object( get_post_type() )->has_archive && kadence()->show_post_navigation() ) : ?>
		<?php if ( kadence()->option( 'post_footer_area_boxed' ) ) { echo '<div class="post-navigation-wrap content-bg entry-content-wrap entry">'; } ?>
		<?php the_post_navigation( apply_filters( 'kadence_post_navigation_args', array(
			'prev_text' => '<div class="post-navigation-sub"><small>' . kadence()->get_icon( 'arrow-left-alt' ) . esc_html__( 'Previous', 'kadence' ) . '</small></div>%title',
			'next_text' => '<div class="post-navigation-sub"><small>' . esc_html__( 'Next', 'kadence' ) . kadence()->get_icon( 'arrow-right-alt' ) . '</small></div>%title',
		) ) ); ?>
		<?php if ( kadence()->option( 'post_footer_area_boxed' ) ) { echo '</div>'; } ?>
	<?php endif; ?>
	<?php if ( kadence()->show_comments() ) { comments_template(); } ?>
<?php endif; ?>