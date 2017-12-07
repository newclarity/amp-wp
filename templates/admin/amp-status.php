<?php
/**
 * AMP status option in the submit meta box.
 *
 * @package AMP
 */

// Check referrer.
if ( ! ( $this instanceof AMP_Post_Meta_Box ) ) {
	return;
}
?>
<div class="misc-pub-section misc-amp-status">
	<span class="amp-icon"></span>
	<?php esc_html_e( 'AMP:', 'amp' ); ?>
	<strong class="amp-status-text"><?php echo esc_html( $labels[ $status ] ); ?></strong>
	<?php if ( $available ) : ?>
		<a href="#amp_status" class="edit-amp-status hide-if-no-js" role="button">
			<span aria-hidden="true"><?php esc_html_e( 'Edit', 'amp' ); ?></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Edit Status', 'amp' ); ?></span>
		</a>
		<div id="amp-status-select" class="hide-if-js" data-amp-status="<?php echo esc_attr( $status ); ?>">
			<input id="amp-status-enabled" type="radio" name="<?php echo esc_attr( self::STATUS_INPUT_NAME ); ?>" value="enabled" <?php checked( ! $disabled ); ?>>
			<label for="amp-status-enabled" class="selectit"><?php echo esc_html( $labels['enabled'] ); ?></label>
			<br />
			<input id="amp-status-disabled" type="radio" name="<?php echo esc_attr( self::STATUS_INPUT_NAME ); ?>" value="disabled" <?php checked( $disabled ); ?>>
			<label for="amp-status-disabled" class="selectit"><?php echo esc_html( $labels['disabled'] ); ?></label>
			<br />
			<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
			<div class="amp-status-actions">
				<a href="#amp_status" class="save-amp-status hide-if-no-js button"><?php esc_html_e( 'OK', 'amp' ); ?></a>
				<a href="#amp_status" class="cancel-amp-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'amp' ); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>
