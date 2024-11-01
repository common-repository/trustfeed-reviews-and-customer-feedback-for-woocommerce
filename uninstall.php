<?php
/**
 * TrustFeed Reviews and Customer Feedback for WooCommerce Uninstall
 *
 * TrustFeed Reviews and Customer Feedback for WooCommerce deletes API Key options.
 *
 * @version 1.1.3
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

//define a vairbale and store an option name as the value.
$option_names = array('trustfeed_data');
//call delete option and use the vairable inside the quotations
// Clear up our settings

foreach($option_names as $option_name) {
    delete_option($option_name);
	// for site options in Multisite
	delete_site_option($option_name);
}















