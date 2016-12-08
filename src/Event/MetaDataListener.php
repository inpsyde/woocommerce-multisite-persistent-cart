<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WooCommerce\MultisitePersistentCart\Event;

/**
 * Interface MetaKeySwitch
 *
 * @package Inpsyde\WooCommerce\MultisitePersistentCart\Event
 */
interface MetaDataListener {

	/**
	 * @wp-hook add_user_metadata
	 *
	 * @param null|bool $default
	 * @param int       $object_id
	 * @param string    $meta_key
	 * @param string    $meta_value
	 * @param bool      $unique
	 *
	 * @return null|bool Return bool to bypass default core meta handling or null to continue
	 */
	public function add_metadata( $default, $object_id, $meta_key, $meta_value, $unique );

	/**
	 * @wp-hook get_user_metadata
	 *
	 * @param null|bool $default
	 * @param int       $object_id
	 * @param string    $meta_key
	 * @param bool      $single
	 *
	 * @return null|bool Return bool to bypass default core meta handling or null to continue
	 */
	public function get_metadata( $default, $object_id, $meta_key, $single );

	/**
	 * @wp-hook update_user_metadata
	 *
	 * @param null| bool $default
	 * @param int        $object_id
	 * @param string     $meta_key
	 * @param string     $meta_value
	 * @param string     $prev_value
	 *
	 * @return null|bool Return bool to bypass default core meta handling or null to continue
	 */
	public function update_metadata( $default, $object_id, $meta_key, $meta_value, $prev_value );

	/**
	 * @wp-hook delete_user_metadata
	 *
	 * @param null|bool $default
	 * @param int       $object_id
	 * @param string    $meta_key
	 * @param string    $meta_value
	 * @param bool      $delete_all
	 *
	 * @return null|bool Return bool to bypass default core meta handling or null to continue
	 */
	public function delete_metadata( $default, $object_id, $meta_key, $meta_value, $delete_all );
}