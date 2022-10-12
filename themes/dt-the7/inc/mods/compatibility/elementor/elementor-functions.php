<?php
/**
 * @package The7
 */

defined( 'ABSPATH' ) || exit;

function the7_elementor_elements_widget_post_types() {
	$post_types = array_intersect_key(
		get_post_types( [], 'object' ),
		[
			'post'            => '',
			'dt_portfolio'    => '',
			'dt_team'         => '',
			'dt_testimonials' => '',
			'dt_gallery'      => '',
		]
	);

	$supported_post_types = [];
	foreach ( $post_types as $post_type ) {
		$supported_post_types[ $post_type->name ] = $post_type->label;
	}

	$supported_post_types['current_query'] = __( 'Archive (current query)', 'the7mk2' );

	return $supported_post_types;
}

function the7_get_public_post_types( $args = [] ) {
	$post_type_args = [
		// Default is the value $public.
		'show_in_nav_menus' => true,
	];

	// Keep for backwards compatibility
	if ( ! empty( $args['post_type'] ) ) {
		$post_type_args['name'] = $args['post_type'];
		unset( $args['post_type'] );
	}

	$post_type_args = wp_parse_args( $post_type_args, $args );

	$_post_types = get_post_types( $post_type_args, 'objects' );

	$post_types = [];

	foreach ( $_post_types as $post_type => $object ) {
		$post_types[ $post_type ] = $object->label;
	}

	/**
	 * Public Post types
	 *
	 * Allow 3rd party plugins to filters the public post types the7 widgets should work on
	 *
	 * @param array $post_types The7 widgets supported public post types.
	 */
	return apply_filters( 'the7_get_public_post_types', $post_types );
}

/**
 * @return string
 */
function the7_elementor_get_message_about_disabled_post_type() {
	return '<p>' . esc_html__( 'The corresponding post type is disabled. Please make sure to 1) install The7 Elements plugin under The7 > Plugins and 2) enable desired post types under The7 > My The7, in the Settings section.', 'the7mk2' ) . '</p>';
}

/**
 * @return mixed
 */
function the7_elementor_get_content_width_string() {
	$content_width = \The7_Elementor_Compatibility::get_elementor_settings( 'container_width' );

	if ( isset( $content_width['size'], $content_width['unit'] ) ) {
		return $content_width['size'] . $content_width['unit'];
	}

	return $content_width;
}