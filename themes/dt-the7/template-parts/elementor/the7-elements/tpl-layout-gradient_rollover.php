<?php
/**
 * Gradient rollover template.
 *
 * @package The7pt
 */

defined( 'ABSPATH' ) || exit;

$rollover_class = '';
if ( ! empty( $icons_html ) ) {
	$rollover_class = 'rollover-active';
}

$placeholder_class = '';
if ( ! has_post_thumbnail() ) {
	$placeholder_class = 'overlay-placeholder';
}
?>

<div class="post-thumbnail-wrap <?php echo $rollover_class; ?>">
	<div class="post-thumbnail <?php echo $placeholder_class; ?>">

		<?php
		if ( ! empty( $post_media ) ) {
			echo $post_media;
		}
		?>

	</div>
</div>

<div class="post-entry-content">
	<div class="post-entry-wrapper">
		<div class="post-entry-body">
			<?php
			if ( ! empty( $icons_html ) ) {
				echo '<div class="project-links-container">' . $icons_html . '</div>';
			}

			if ( ! empty( $post_title ) ) {
				echo $post_title;
			}

			if ( ! empty( $post_meta ) ) {
				echo $post_meta;
			}

			if ( ! empty( $post_excerpt ) ) {
				echo '<div class="entry-excerpt">';
				echo $post_excerpt;
				echo '</div>';
			}

			if ( ! empty( $details_btn ) ) {
				echo $details_btn;
			}
			?>
		</div>
	</div>
</div>