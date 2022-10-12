<?php
/**
 * Mixed header branding.
 *
 * @since   3.0.0
 * @package The7/Templates
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="branding">

	<?php
	$logo = '';

	$logo .= presscore_get_the_mixed_logo();
	$logo .= presscore_get_the_mobile_logo();

	$logo_class = '';

	if ( of_get_option( 'header-style-mixed-top_line-floating-choose_logo' ) === 'main' && ( ! presscore_header_is_transparent() || of_get_option( 'header-style-mixed-transparent-top_line-choose_logo') === 'main') && presscore_config()->get('header.layout') === 'top_line' ) {
		$logo_class = 'same-logo';
	}

	presscore_display_the_logo( $logo, $logo_class );
	?>

</div>
