<?php
/*
    Plugin Name: Swish (Manual) - WooCommerce Gateway
    Plugin URI: http://redlight.se/swish
    Description: Extends WooCommerce by Swish Gateway.
    Version: 1.0.1
    Author: Christopher Hedqvist, Redlight Media AB
    Author URI: http://www.redlight.se/
*/

/* Initiate redlight swish when all plugins have loaded */
add_action('plugins_loaded', array('WooCommerce_Swish_Gateway', 'init_woocommerce_swish_gateway'), 0);

class WooCommerce_Swish_Gateway {

    /**
     * Initiate the payment gateway.
     *
     * @access public
     * @return void
     */

    public static function init_woocommerce_swish_gateway() {

        if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

        /*  Require woocommerce-swish which contains all the functionality and settings required
            for configuring and using the gateway
        */
        require_once('woocommerce-swish.php');

        add_filter('woocommerce_payment_gateways', array('WooCommerce_Swish_Gateway', 'add_woocommerce_swish_gateway') );

    }

    /**
     * Add the gateway to WooCommerce.
     *
     * @access public
     * @param array $methods
     * @return array
     */

    public static function add_woocommerce_swish_gateway( $methods ) {

        $methods[] = 'WooCommerce_Swish';
        return $methods;

    }

}