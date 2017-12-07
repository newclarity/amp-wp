<?php
/**
 * AMP Post type support.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Class AMP_Post_Type_Support.
 */
class AMP_Post_Type_Support {

	/**
	 * Get post types that plugin supports out of the box (which cannot be disabled).
	 *
	 * @return string[] Post types.
	 */
	public static function get_builtin_supported_post_types() {
		return array_filter( array( 'post' ), 'post_type_exists' );
	}

	/**
	 * Get post types that are eligible for AMP support.
	 *
	 * @since 0.6
	 * @return string[] Post types eligible for AMP.
	 */
	public static function get_eligible_post_types() {
		return array_merge(
			self::get_builtin_supported_post_types(),
			array_values( get_post_types(
				array(
					'public'   => true,
					'_builtin' => false,
				),
				'names'
			) )
		);
	}

	/**
	 * Declare support for post types.
	 *
	 * This function should only be invoked through the 'after_setup_theme' action to
	 * allow plugins/theme to overwrite the post types support.
	 *
	 * @since 0.6
	 */
	public static function add_post_type_support() {
		$post_types = array_merge(
			self::get_builtin_supported_post_types(),
			AMP_Options_Manager::get_option( 'supported_post_types', array() )
		);
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, AMP_QUERY_VAR );
		}
	}
}
