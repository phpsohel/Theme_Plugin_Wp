<?php
/**
 * Meta Box connection
 *
 * @since 3.3.2
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Include metaboxes overrides.
 *
 */
require_once( PRESSCORE_EXTENSIONS_DIR . '/custom-meta-boxes/override-fields.php' ); 

/**
 * Include Meta-Box framework.
 *
 */
require_once( THE7_RWMB_DIR . 'meta-box.php' );

/**
 * Include custom metaboxes.
 *
 */
require_once( PRESSCORE_EXTENSIONS_DIR . '/custom-meta-boxes/metabox-fields.php' );
require_once( PRESSCORE_EXTENSIONS_DIR . '/custom-meta-boxes/class-the7-rwmb-dimension-field.php' );

/**
 * Register meta boxes
 */
function presscore_register_meta_boxes() {
	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( !class_exists( 'The7_RW_Meta_Box' ) ) {
		return;
	}

	global $DT_META_BOXES;

	do_action( 'the7_before_meta_box_registration' );

	foreach ( $DT_META_BOXES as $meta_box ) {
		new The7_RW_Meta_Box( $meta_box );
	}
}
add_action( 'admin_init', 'presscore_register_meta_boxes', 30 );

/**
 * Define default meta boxes for templates
 *
 * @TODO: Delete in the future.
 *
 * @param  array $hidden Hidden Meta Boxes
 * @param  string|WP_Screen $screen Current screen
 * @param  bool $use_defaults Use default Meta Boxes or not
 * 
 * @return array Hidden Meta Boxes
 */
function presscore_hidden_meta_boxes( $hidden, $screen, $use_defaults ) {
	$template   = dt_get_template_name();
	$meta_boxes = the7_get_meta_boxes_with_template_dependencies();

	foreach ( $meta_boxes as $meta_box ) {
		if ( in_array( $template, (array) $meta_box['only_on']['template'], true ) ) {
			$meta_box_key_to_show = array_search( $meta_box['id'], $hidden, true );
			if ( false !== $meta_box_key_to_show ) {
				unset( $hidden[ $meta_box_key_to_show ] );
			}
		} else {
			$hidden[] = $meta_box['id'];
		}
	}

	return array_unique( $hidden );
}
//add_filter('hidden_meta_boxes', 'presscore_hidden_meta_boxes', 99, 3);

/**
 * @return array
 */
function the7_get_meta_boxes_with_template_dependencies() {
	global $DT_META_BOXES;

	$meta_boxes = array();

	foreach ( $DT_META_BOXES as $meta_box ) {
		if ( isset( $meta_box['only_on']['template'] ) ) {
			$meta_boxes[] = $meta_box;
		}
	}

	return $meta_boxes;
}