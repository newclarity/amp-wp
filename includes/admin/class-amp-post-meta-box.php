<?php
/**
 * AMP meta box settings.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Post meta box class.
 *
 * @since 0.6
 */
class AMP_Post_Meta_Box {

	/**
	 * Assets handle.
	 *
	 * @since 0.6
	 * @var string
	 */
	const ASSETS_HANDLE = 'amp-post-meta-box';

	/**
	 * The post meta key for whether the post is skipped.
	 *
	 * @since 0.6
	 * @var string
	 */
	const DISABLED_POST_META_KEY = 'amp_disabled';

	/**
	 * The field name for the enabled/disabled radio buttons.
	 *
	 * @since 0.6
	 * @var string
	 */
	const STATUS_INPUT_NAME = 'amp_status';

	/**
	 * The nonce name.
	 *
	 * @since 0.6
	 * @var string
	 */
	const NONCE_NAME = 'amp-status-nonce';

	/**
	 * The nonce action.
	 *
	 * @since 0.6
	 * @var string
	 */
	const NONCE_ACTION = 'amp-update-status';

	/**
	 * Initialize.
	 *
	 * @since 0.6
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'render_status' ) );
		add_action( 'save_post', array( $this, 'save_amp_status' ) );
		add_filter( 'preview_post_link', array( $this, 'preview_post_link' ) );
	}

	/**
	 * Get whether AMP is available for a given post.
	 *
	 * This is just calling `post_supports_amp()` but ignoring any user-supplied opt-out for AMP.
	 *
	 * @since 0.6
	 * @see post_supports_amp()
	 *
	 * @param WP_Post $post Post.
	 * @return bool Whether or not AMP is available.
	 */
	protected function is_amp_available( $post ) {
		add_filter( 'get_post_metadata', '__return_empty_string', 1000 );
		$available = post_supports_amp( $post );
		remove_filter( 'get_post_metadata', '__return_empty_string', 1000 );
		return $available;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 0.6
	 */
	public function enqueue_admin_assets() {
		$post     = get_post();
		$screen   = get_current_screen();
		$validate = (
			isset( $screen->base )
			&&
			'post' === $screen->base
			&&
			post_type_supports( $post->post_type, AMP_QUERY_VAR )
		);
		if ( ! $validate ) {
			return;
		}

		// Styles.
		wp_enqueue_style(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'css/amp-post-meta-box.css' ),
			false,
			AMP__VERSION
		);

		// Scripts.
		wp_enqueue_script(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'js/amp-post-meta-box.js' ),
			array( 'jquery' ),
			AMP__VERSION
		);
		wp_add_inline_script( self::ASSETS_HANDLE, sprintf( 'ampPostMetaBox.boot( %s );',
			wp_json_encode( array(
				'previewLink'     => esc_url_raw( add_query_arg( AMP_QUERY_VAR, '', get_preview_post_link( $post ) ) ),
				'disabled'        => (bool) get_post_meta( $post->ID, self::DISABLED_POST_META_KEY, true ) || ! $this->is_amp_available( $post ),
				'statusInputName' => self::STATUS_INPUT_NAME,
				'l10n'            => array(
					'ampPreviewBtnLabel' => __( 'Preview changes in AMP (opens in new window)', 'amp' ),
				),
			) )
		) );
	}

	/**
	 * Render AMP status.
	 *
	 * @since 0.6
	 * @param WP_Post $post Post.
	 */
	public function render_status( $post ) {
		$verify = (
			isset( $post->ID )
			&&
			isset( $post->post_type )
			&&
			post_type_supports( $post->post_type, AMP_QUERY_VAR )
			&&
			current_user_can( 'edit_post', $post->ID )
		);

		if ( true !== $verify ) {
			return;
		}

		$available = $this->is_amp_available( $post );
		$disabled  = (bool) get_post_meta( $post->ID, self::DISABLED_POST_META_KEY, true );
		$status    = $disabled || ! $available ? 'disabled' : 'enabled';
		$labels    = array(
			'enabled'  => __( 'Enabled', 'amp' ),
			'disabled' => __( 'Disabled', 'amp' ),
		);

		// The preceding variables are used inside the following amp-status.php template.
		include_once AMP__DIR__ . '/templates/admin/amp-status.php';
	}

	/**
	 * Save AMP Status.
	 *
	 * @since 0.6
	 * @param int $post_id The Post ID.
	 */
	public function save_amp_status( $post_id ) {
		$verify = (
			isset( $_POST[ self::NONCE_NAME ] )
			&&
			isset( $_POST[ self::STATUS_INPUT_NAME ] )
			&&
			wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION )
			&&
			current_user_can( 'edit_post', $post_id )
			&&
			! wp_is_post_revision( $post_id )
			&&
			! wp_is_post_autosave( $post_id )
		);

		if ( true === $verify ) {
			if ( 'disabled' === $_POST[ self::STATUS_INPUT_NAME ] ) {
				update_post_meta( $post_id, self::DISABLED_POST_META_KEY, true );
			} else {
				delete_post_meta( $post_id, self::DISABLED_POST_META_KEY );
			}
		}
	}

	/**
	 * Modify post preview link.
	 *
	 * Add the AMP query var is the amp-preview flag is set.
	 *
	 * @since 0.6
	 *
	 * @param string $link The post preview link.
	 * @return string Preview URL.
	 */
	public function preview_post_link( $link ) {
		$is_amp = (
			isset( $_POST['amp-preview'] ) // WPCS: CSRF ok.
			&&
			'do-preview' === sanitize_key( wp_unslash( $_POST['amp-preview'] ) ) // WPCS: CSRF ok.
		);

		if ( $is_amp ) {
			$link = add_query_arg( AMP_QUERY_VAR, true, $link );
		}

		return $link;
	}

}
