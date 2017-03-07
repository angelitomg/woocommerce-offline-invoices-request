<?php
/* @wordpress-plugin
 * Plugin Name:       WooCommerce Offline Invoices Request
 * Plugin URI:        
 * Description:       Request offline invoices as payment method
 * Version:           1.0.0
 * Author:            AMG Labs
 * Author URI:        https://amglabs.net
 * Text Domain:       woocommerce-offline-invoices-request
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if(in_array('woocommerce/woocommerce.php', $active_plugins)){
	add_filter('woocommerce_payment_gateways', 'add_offline_invoices_request');
	function add_offline_invoices_request( $gateways ){
		$gateways[] = 'WC_Offline_Invoices_Request';
		return $gateways; 
	}

	add_action('plugins_loaded', 'init_offline_invoices_request');
	function init_offline_invoices_request(){
		require 'class-woocommerce-offline-invoices-request.php';
	}

	add_action( 'plugins_loaded', 'offline_invoices_request_load_plugin_textdomain' );
	function offline_invoices_request_load_plugin_textdomain() {
	  load_plugin_textdomain( 'woocommerce-offline-invoices-request', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
}