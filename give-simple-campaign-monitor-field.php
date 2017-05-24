<?php
/*
Plugin Name: Give - Simple Campaign Monitor Field
Plugin URI:  https://github.com/squarecandy/give-simple-fields
Description: Add users to a Campaign Monitor account automatically if they keep the "please add me to the email list" box checked.
Version:	 1.0
Author:	  Square Candy
Author URI:  http://squarecandy.net
License:	 GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Text Domain: give-simple-campaign-monitor-field
*/


// Add an Email List checkbox to all the Give forms
function give_simple_campaign_monitor_field_squarecandy( $form_id ) {
	?>
	<p id="give-simple-campaign-monitor-field-container" class="form-row">
		<label class="give-label" for="give-simple-campaign-monitor-field">
			<input type="checkbox" checked="checked" name="give_simple_campaign_monitor_field" id="give-simple-campaign-monitor-field">
			<?php
			$label = 'Please add me to the ' . get_bloginfo('name') . ' email list.';
			echo __( $label, 'give-simple-campaign-monitor-field' ); ?>
		</label>
	</p>
	<?php
}
add_action( 'give_donation_form_before_cc_form', 'give_simple_campaign_monitor_field_squarecandy', 10, 1 );


function give_simple_campaign_monitor_field_squarecandy_store( $_POST, $user_info, $valid_data ) {
	print '<script>alert("test");</script>';
	return false;
}
add_filter( 'give_checkout_before_gateway', 'give_simple_campaign_monitor_field_squarecandy_store', 10, 2 );
