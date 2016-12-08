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

use Inpsyde\WooCommerce\MultisitePersistentCart\Event\MetaDataListenerProvider;
use Inpsyde\WooCommerce\MultisitePersistentCart\Storage\UserMetaKeySwitch;

add_action(
	'before_woocommerce_init',
	function () {

		is_readable( __DIR__ . '/vendor/autoload.php' ) && require_once __DIR__ . '/vendor/autoload.php';

		$key_map = function ( $key ) {

			if ( '_woocommerce_persistent_cart' !== $key ) {
				return $key;
			}

			return '_woocommerce_persistent_cart_' . get_current_blog_id();
		};

		( new MetaDataListenerProvider(
			new UserMetaKeySwitch( $key_map ),
			'user'
		) )->provide_listener();
	}
);