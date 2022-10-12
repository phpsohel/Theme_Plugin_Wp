<?php
/**
 * @package The7
 */

defined( 'ABSPATH' ) || exit;

/**
 * @var string $follow_link
 * @var string $caption
 * @var string $icon
 * @var string $icon_position
 * @var string $aria_label
 */
?>

<a class="post-details details-type-btn dt-btn-s dt-btn" href="<?php echo esc_url( $follow_link ) ?>" aria-label="<?php echo esc_attr( $aria_label ) ?>" rel="nofollow"><?php
	if ( $icon_position === 'before' ) {
		echo $icon;
	}

	echo '<span>' . esc_html( $caption ) . '</span>';

	if ( $icon_position === 'after' ) {
		echo $icon;
	}
	?></a>