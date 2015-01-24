<?php
/*
Plugin Name: Swish (Manual) - WooCommerce Gateway
Plugin URI: http://redlight.se/swish
Description: Extends WooCommerce by Swish Gateway.
Version: 1.0
Author: Christopher Hedqvist, Redlight Media AB
Author URI: http://www.redlight.se/
*/
 
// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'redlight_swish_init', 0 );
function redlight_swish_init() {
    // If the parent WC_Payment_Gateway class doesn't exist
    // it means WooCommerce is not installed on the site
    // so do nothing
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
     
    // If we made it this far, then include our Gateway Class
    include_once( 'woocommerce-swish.php' );
 
    // Now that we have successfully included our class,
    // Lets add it too WooCommerce
    add_filter( 'woocommerce_payment_gateways', 'spyr_add_swish_gateway' );
    function spyr_add_swish_gateway( $methods ) {
        $methods[] = 'redlight_swish';
        return $methods;
    }
}
 
// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'redlight_swish_action_links' );
function redlight_swish_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'redlight-swish' ) . '</a>',
    );
 
    // Merge our new link with the default ones
    return array_merge( $plugin_links, $links );    
}