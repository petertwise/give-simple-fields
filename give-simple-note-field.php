<?php
/*
Plugin Name: Give - Simple Note Field
Plugin URI:  https://github.com/squarecandy/give-simple-fields
Description: Simply adds a note or comment field to the Give donations plugin.
Version:	 1.0
Author:	  Square Candy
Author URI:  http://squarecandy.net
License:	 GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Text Domain: give-simple-note-field
*/


// Add a "Note" field to all the Give forms
function give_simple_note_field_squarecandy( $form_id ) {
		?>
		<p id="give-simple-note-field-container" class="form-row">
			<label class="give-label" for="give-simple-note-field"><?php _e( 'Note:', 'give-simple-note-field' ); ?></label>
			<textarea class="give-textarea" name="give_simple_note_field" id="give-simple-note-field"></textarea>
		</p>
	<?php
}
add_action( 'give_donation_form_after_cc_form', 'give_simple_note_field_squarecandy', 10, 1 );

// Store the field submission in the database
function give_simple_note_field_squarecandy_store( $payment_meta ) {
	if ( isset( $_POST['give_simple_note_field'] ) ) {
		$payment_meta['give_simple_note_field'] = esc_textarea( $_POST['give_simple_note_field'] );
	}
	return $payment_meta;
}
add_filter( 'give_payment_meta', 'give_simple_note_field_squarecandy_store', 10 );

// Display the field when viewing the transaction in the Dashboard
function give_simple_note_field_squarecandy_admin_display( $payment_meta, $user_info ) {
	$payment_id = $_GET['id'];
	$give_meta = get_post_meta( $payment_id, '_give_payment_meta', true );
	// pre_r($give_meta);
	if ( isset( $give_meta['give_simple_note_field'] ) && !empty( $give_meta['give_simple_note_field'] ) ) : ?>
	<p class="give-simple-note-field" style="margin: 1em 0;">
		<strong><?php echo __( 'Donor Checkout Note:', 'give' ); ?></strong><br/>
		<?php echo $give_meta['give_simple_note_field']; // echo wpautop( $give_meta['give_simple_note_field'] ); ?>
	</p>
	<?php endif;
}
add_action( 'give_payment_personal_details_list', 'give_simple_note_field_squarecandy_admin_display', 5, 2 );

// Make the field available to the Give email system
function give_simple_note_field_squarecandy_email_tag( $payment_id ) {
	give_add_email_tag( 'simple_note_field', 'This tag outputs the custom note field', 'give_simple_note_field_squarecandy_data' );
}
add_action( 'give_add_email_tags', 'give_simple_note_field_squarecandy_email_tag' );

function give_simple_note_field_squarecandy_data( $payment_id ) {
	$payment_meta = give_get_payment_meta( $payment_id );
	$output       = '';
	if ( isset( $payment_meta['give_simple_note_field'] ) && ! empty( $payment_meta['give_simple_note_field'] ) ) {
		$output = $payment_meta['give_simple_note_field'];
	}
	return $output;
}
