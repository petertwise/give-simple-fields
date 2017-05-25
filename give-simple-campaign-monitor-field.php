<?php
/*
Plugin Name: Give - Simple Candy Mail Checkbox
Plugin URI:  https://github.com/squarecandy/give-simple-fields
Description: Add users to a Candy Mail (Campaign Monitor) account automatically if they keep the "please add me to the email list" box checked.
Version:	 1.0
Author:	  Square Candy
Author URI:  http://squarecandydesign.com
License:	 GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Text Domain: give-simple-campaign-monitor-field
*/

// SETTINGS:
define( 'CM_ACCOUNT_SLUG', 'squarecandydesign' );
define( 'CM_URL_CODE', 'abcdef' );

// don't let users activate w/o GIVE
register_activation_hook( __FILE__, 'give_simple_campaign_monitor_field_activate' );
function give_simple_campaign_monitor_field_activate(){
	if ( ! is_plugin_active( 'give/give.php' ) ) {

		// check that ACF functions we need are available. Complain and bail out if they are not
		wp_die('Sorry, the Simple Campaign Monitor Field Plugin requires the
			<a href="https://givewp.com/">Give Donations Plugin</a>.
			<br><br><button onclick="window.history.back()">&laquo; back</button>');
	}
}

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

		// mark the user as signed up in the Give data
		$payment_meta['give_simple_campaign_monitor_field'] = true;

		// try to get the Campaign Monitor url code from options
		// you can create an options field called "candy_mail_url_code"
		// with ACF to use this feature
		$cmid = get_field('candy_mail_url_code', 'options');
		$cmslug = get_field('candy_mail_account_slug', 'options');
		if ( !$cmid ) {
			$cmid = CM_URL_CODE;
		}
		if ( !$cmslug ) {
			$cmslug = CM_ACCOUNT_SLUG;
		}
		if ( !empty($cmid) && !empty($cmslug) ) {

			$content['cm-'.$cmid.'-'.$cmid] = $_POST['give_email'];
			$content['cm-name'] = $_POST['give_first'] . " " . $_POST['give_last'];

			// build the required data for the form
			$url = 'https://' . $cmslug . '.createsend.com/t/r/s/' . $cmid . '/';
			$headers = array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8');
			$options = array('headers' => $headers, 'body' => $content, 'timeout' => '60');

			// send the data
			$response = wp_remote_post( $url, $options );

			if ($response) {
				// responses are weird from Campaign Monitor.
				// May contain a full HTML page with some settings
				// May look like success, but subscription failed.
				// Just log them, don't display anything to the user indicating success or failure
				// The primary goal is the donation UX. The list signup is secondary.
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
		<?php endif; */ ?>
	<?php
	endif;
}
add_action( 'give_payment_personal_details_list', 'give_simple_campaign_monitor_field_squarecandy_admin_display', 50, 2 );
