<?php
/**
 * @package The7
 */

defined( 'ABSPATH' ) || exit;

/**
 * @var string $href
 * @var string $class
 * @var string $aria_label
 * @var string $caption
 */
?>

<a href="<?php echo esc_url( $href ) ?>" class="<?php echo esc_attr( $class ) ?>" aria-label="<?php echo esc_attr( $aria_label ) ?>" rel="nofollow"><?php echo $caption ?></a>
