<?php
/**
 * Entry header for forecast single posts — adds meta + separator
 */
namespace Kadence;
use function Kadence\kadence;
?>
<header class="entry-header">
	<?php do_action( 'kadence_single_before_entry_header' ); ?>
	<?php kadence()->render_title( get_post_type(), 'normal' ); ?>
	<div class="entry-meta entry-meta-divider-dot">
		<span class="posted-by">
			<span class="author vcard">
				<a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( get_the_author() ); ?></a>
			</span>
		</span>
		<span class="posted-on">
			<time class="entry-date published" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		</span>
	</div>
	<hr style="border:none;border-top:1px solid rgba(255,255,255,0.1);margin:0.5rem 0 1.5rem 0">
	<?php do_action( 'kadence_single_after_entry_header' ); ?>
</header>