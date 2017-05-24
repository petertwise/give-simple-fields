<?php
/*
Plugin Name: Give - Simple Anonymous Field
Plugin URI:  https://github.com/squarecandy/give-simple-fields
Description: Simply adds a note or comment field to the Give donations plugin.
Version:	 1.0
Author:	  Square Candy
Author URI:  http://squarecandy.net
License:	 GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Text Domain: give-simple-anon-field
*/


// Add a "Note" field to all the Give forms
function give_simple_anon_field_squarecandy( $form_id ) {
	?>
	<p id="give-simple-anon-field-container" class="form-row">
		<label class="give-label" for="give-simple-anon-field">
			<input type="checkbox" name="give_simple_anon_field" id="give-simple-anon-field">
			<?php echo __( 'Please make this donation anonymous.', 'give-simple-anon-field' ); ?>
		</label>
	</p>
	<?php
}
add_action( 'give_donation_form_before_cc_form', 'give_simple_anon_field_squarecandy', 10, 1 );

// Store the field submission in the database
function give_simple_anon_field_squarecandy_store( $payment_meta ) {
	$payment_meta['give_simple_anon_field'] = isset( $_POST['give_simple_anon_field'] ) ? implode( "n", array_map( 'sanitize_text_field', explode( "n", $_POST['give_simple_anon_field'] ) ) ) : '';
	return $payment_meta;
}
add_filter( 'give_payment_meta', 'give_simple_anon_field_squarecandy_store' );

// Display the field when viewing the transaction in the Dashboard
function give_simple_anon_field_squarecandy_admin_display( $payment_meta, $user_info ) {
	// Bounce out if no data for this transaction
	if ( ! isset( $payment_meta['referral'] ) ) {
		return;
	}
	if ( $payment_meta['give_simple_anon_field'] ) :
	?>
	<div class="give-simple-anon-field">
		<p><?php echo __( 'Anonymous Donation', 'give-simple-anon-field' ); ?></p>
	</div>
	<?php
	endif;
}
add_action( 'give_payment_personal_details_list', 'give_simple_anon_field_squarecandy_admin_display', 10, 2 );

// Make the field available to the Give email system
function give_simple_anon_field_squarecandy_email_tag( $payment_id ) {
	give_add_email_tag( 'simple_anon_field', 'This tag outputs "Anonymous Donation" if the box has been checked', 'give_simple_anon_field_squarecandy_data' );
}
add_action( 'give_add_email_tags', 'give_simple_anon_field_squarecandy_email_tag' );

function give_simple_anon_field_squarecandy_data( $payment_id ) {
	$payment_meta = give_get_payment_meta( $payment_id );
	$output       = '';
	if ( isset($payment_meta['give_simple_anon_field']) && $payment_meta['give_simple_anon_field'] ) {
		$output = __( 'Anonymous Donation', 'give-simple-anon-field' );
	}
	return $output;
}
