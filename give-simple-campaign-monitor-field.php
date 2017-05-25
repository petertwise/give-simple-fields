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
			echo __( $label, 'give' ); ?>
		</label>
	</p>
	<?php
}
add_action( 'give_donation_form_before_cc_form', 'give_simple_campaign_monitor_field_squarecandy', 10, 1 );


function give_simple_campaign_monitor_field_squarecandy_store( $payment_meta ) {
	if ( function_exists('get_field') &&
		isset( $_POST['give_simple_campaign_monitor_field'] ) &&
		$_POST['give_simple_campaign_monitor_field'] == "on" ) :

		$payment_meta['give_simple_campaign_monitor_field'] = true;

		// do the campaign monitor stuff...
		$cmid = get_field('candy_mail_url_code', 'options');

		if ($cmid) {

			$content['cm-'.$cmid.'-'.$cmid] = $_POST['give_email'];
			$content['cm-name'] = $_POST['give_first'] . " " . $_POST['give_last'];

			// build the required data for the form
			$url = 'https://squarecandydesign.createsend.com/t/r/s/' . $cmid . '/';
			$headers = array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8');
			$options = array('headers' => $headers, 'body' => $content, 'timeout' => '60');

			// send the data
			$response = wp_remote_post( $url, $options );

			if ($response) {
				$payment_meta['give_simple_campaign_monitor_field_response'] = $response;
			}
		}
		else {
			$payment_meta['give_simple_campaign_monitor_field_response'] = "Please set the Candy Mail URL Code field on the settings page.";
		}

	else:
		$payment_meta['give_simple_campaign_monitor_field_response'] = "Oops. The ACF plugin is required. You also have to set your Candy Mail URL Code on the settings page.";
	endif;
	return $payment_meta;
}
add_filter( 'give_payment_meta', 'give_simple_campaign_monitor_field_squarecandy_store', 999);

// Display the field when viewing the transaction in the Dashboard
function give_simple_campaign_monitor_field_squarecandy_admin_display( $payment_meta, $user_info ) {
	$payment_id = $_GET['id'];
	$give_meta = get_post_meta( $payment_id, '_give_payment_meta', true );
	if ( isset( $give_meta['give_simple_campaign_monitor_field'] ) && $give_meta['give_simple_campaign_monitor_field'] ) : ?>
	<p class="give-simple-campaign_monitor-field">
		<strong><?php echo __( '✅ Signed Up for the Email List', 'give' ); ?></strong>
	</p>
		<?php /* if ( WP_DEBUG ) : ?>
		<details>
			<summary style="color: #aaa;"><small>candy-mail debug</small></summary>
			<pre><?php print_r($give_meta['give_simple_campaign_monitor_field_response']); ?></pre>
		</details>
		<?php endif; */
	endif;
}
add_action( 'give_payment_personal_details_list', 'give_simple_campaign_monitor_field_squarecandy_admin_display', 50, 2 );
