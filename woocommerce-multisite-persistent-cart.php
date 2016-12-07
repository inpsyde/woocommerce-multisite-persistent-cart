<?php # -*- coding: utf-8 -*-

/**
 * Plugin Name: WooCommerce Multisite Persistent Cart
 * Description: Multisite aware persistent storage for user carts
 * Plugin URI:  TODO
 * Author:      Inpsyde GmbH
 * Author URI:  http://inpsyde.com/
 * Version:     dev-master
 * License:     MIT
 * Text Domain: woocommerce-multisite-persistent-cart
 * Multisite:   true
 */

namespace Inpsyde\WooCommerce\MultisitePersistentCart;

add_action( 'before_woocommerce_init', function() {

	is_readable( __DIR__ . '/vendor/autoload.php' ) && require_once __DIR__ . '/vendor/autoload.php';


} );