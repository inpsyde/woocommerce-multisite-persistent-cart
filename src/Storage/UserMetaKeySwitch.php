<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WooCommerce\MultisitePersistentCart\Storage;

use Inpsyde\WooCommerce\MultisitePersistentCart\Event\MetaDataListener;

/**
 * Class MetaKeySwitch
 *
 * @package Inpsyde\WooCommerce\MultisitePersistentCart\Storage
 */
final class UserMetaKeySwitch implements MetaDataListener {

	/**
	 * @var callable
	 */
	private $key_map;

	/**
	 * @var bool
	 */
	private $override = TRUE;

	/**
	 * @var bool
	 */
	private $bypass_add = FALSE;

	/**
	 * @var bool
	 */
	private $bypass_get = FALSE;

	/**
	 * @var bool
	 */
	private $bypass_update = FALSE;

	/**
	 * @var bool
	 */
	private $bypass_delete = FALSE;

	/**
	 * @param callable $key_map
	 * @param bool     $override (Whether to completely replace the default key on write actions)
	 */
	public function __construct( callable $key_map, $override = TRUE ) {

		$this->key_map  = $key_map;
		$this->override = (bool) $override;
	}

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
	public function add_metadata( $default, $object_id, $meta_key, $meta_value, $unique ) {

		if ( $this->bypass_add || ! $this->switch_key( $meta_key, $object_id, $meta_value ) ) {
			return $default;
		}

		$mapped_key       = $this->mapped_key( $meta_key, $object_id, $meta_value );
		$this->bypass_add = TRUE; // Just in case the key_map messed up
		$result           = add_user_meta( $object_id, $mapped_key, $meta_value, $unique );
		$this->bypass_add = FALSE;

		return $this->override
			? $result
			: $default;
	}

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
	public function get_metadata( $default, $object_id, $meta_key, $single ) {

		if ( $this->bypass_get || ! $this->switch_key( $meta_key, $object_id ) ) {
			return $default;
		}

		$mapped_key       = $this->mapped_key( $meta_key, $object_id );
		$this->bypass_get = TRUE;
		$result           = get_user_meta( $object_id, $mapped_key, $single );
		$this->bypass_get = FALSE;

		return metadata_exists( 'user', $object_id, $mapped_key )
			? $result
			: $default; // Fall back to the default value if no current key found
	}

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
	public function update_metadata( $default, $object_id, $meta_key, $meta_value, $prev_value ) {

		if ( $this->bypass_update || ! $this->switch_key( $meta_key, $object_id, $meta_value ) ) {
			return $default;
		}

		$mapped_key          = $this->mapped_key( $meta_key, $object_id, $meta_value );
		$this->bypass_update = TRUE;
		$result              = update_user_meta( $object_id, $mapped_key, $meta_value, $prev_value );
		$this->bypass_update = FALSE;

		return $this->override
			? $result
			: $default;
	}

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
	public function delete_metadata( $default, $object_id, $meta_key, $meta_value, $delete_all ) {

		if ( $this->bypass_delete || ! $this->switch_key( $meta_key, $object_id, $meta_value ) ) {
			return $default;
		}

		$mapped_key          = $this->mapped_key( $meta_key, $object_id, $meta_value );
		$this->bypass_delete = TRUE;
		delete_user_meta( $object_id, $mapped_key, $meta_value );
		$this->bypass_delete = FALSE;

		// The original key should be always deleted
		return $default;
	}

	/**
	 * @param string $key
	 * @param int    $object_id
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	private function switch_key( $key, $object_id, $value = NULL ) {

		$mapped_key = $this->mapped_key( $key, $object_id, $value );

		return $key !== $mapped_key && ! empty( $mapped_key );
	}

	/**
	 * @param string $key
	 * @param int    $object_id
	 * @param mixed  $value
	 *
	 * @return string
	 */
	private function mapped_key( $key, $object_id, $value = NULL ) {

		return call_user_func_array( $this->key_map, func_get_args() );
	}
}