<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WooCommerce\MultisitePersistentCart\Storage;

use Brain\Monkey\Functions;
use MonkeryTestCase\BrainMonkeyWpTestCase;

/**
 * Class UserMetaKeySwitchTest
 *
 * @package Inpsyde\WooCommerce\MultisitePersistentCart\Storage
 */
class UserMetaKeySwitchTest extends BrainMonkeyWpTestCase {

	/**
	 * @see          UserMetaKeySwitch::add_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_add_metadata( array $meta_record ) {

		$result  = TRUE;
		$default = NULL;
		$key_map = function ( $key ) use ( $meta_record ) {

			return $meta_record[ 'key' ] === $key
				? $meta_record[ 'mapped_key' ]
				: $key;
		};

		Functions::expect( 'add_user_meta' )
			->twice()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'mapped_key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'unique' ]
			)
			->andReturn( $result );

		$meta_key_switch_override = new UserMetaKeySwitch( $key_map, TRUE );
		$meta_key_switch          = new UserMetaKeySwitch( $key_map, FALSE );

		$this->assertSame(
			$result,
			$meta_key_switch_override->add_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'unique' ]
			)
		);

		$this->assertSame(
			$default,
			$meta_key_switch->add_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'unique' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::add_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_add_metadata_ignores_empty_map( array $meta_record ) {

		$key_map = function () {

			return NULL;
		};
		Functions::expect( 'add_user_meta' )
			->never();

		$testee = new UserMetaKeySwitch( $key_map );

		$this->assertNull(
			$testee->add_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'unique' ]
			)
		);
	}

	/**
	 * Recursion can happen when the key_map fails
	 *
	 * @see          UserMetaKeySwitch::add_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_add_metadata_not_recurses( array $meta_record ) {

		$key_map = function ( $key ) {

			return "{$key}_next";
		};
		$result  = TRUE;
		$default = NULL;
		$testee  = new UserMetaKeySwitch( $key_map );

		Functions::expect( 'add_user_meta' )
			->once()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ] . '_next',
				$meta_record[ 'value' ],
				$meta_record[ 'unique' ]
			)
			->andReturnUsing(
				function ( ...$args ) use ( $testee, $result, $default ) {

					// Recurse like `add_user_meta` would do via `add_metadata` filter
					$this->assertSame(
						$default,
						$testee->add_metadata( $default, ...$args )
					);

					return $result;
				}
			);

		$this->assertSame(
			$result,
			$testee->add_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'unique' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::get_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_get_metadata( array $meta_record ) {

		$key_map = function ( $key ) use ( $meta_record ) {

			return $meta_record[ 'key' ] === $key
				? $meta_record[ 'mapped_key' ]
				: $key;
		};

		Functions::expect( 'get_user_meta' )
			->once()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'mapped_key' ],
				$meta_record[ 'single' ]
			)
			->andReturn( $meta_record[ 'value' ] );

		Functions::expect( 'metadata_exists' )
			->once()
			->with( 'user', $meta_record[ 'user_id' ], $meta_record[ 'mapped_key' ] )
			->andReturn( TRUE );

		$testee = new UserMetaKeySwitch( $key_map );
		$this->assertSame(
			$meta_record[ 'value' ],
			$testee->get_metadata(
				'whatever', $meta_record[ 'user_id' ], $meta_record[ 'key' ], $meta_record[ 'single' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::get_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_get_metadata_ignores_empty_map( array $meta_record ) {

		$key_map = function () {

			return NULL;
		};

		Functions::expect( 'get_user_meta' )
			->never();

		$testee = new UserMetaKeySwitch( $key_map );

		$this->assertNull(
			$testee->get_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'single' ]
			)
		);
	}

	/**
	 * Test that the method falls back to the original value if the mapped
	 * key does not exist.
	 *
	 * @see          UserMetaKeySwitch::get_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_get_metadata_falls_back_to_default( array $meta_record ) {

		$key_map = function ( $key ) use ( $meta_record ) {

			return $meta_record[ 'key' ] === $key
				? $meta_record[ 'mapped_key' ]
				: $key;
		};
		$default = 'whatever';

		Functions::expect( 'get_user_meta' )
			->once()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'mapped_key' ],
				$meta_record[ 'single' ]
			)
			->andReturn( $meta_record[ 'value' ] );

		Functions::when( 'metadata_exists' )
			->justReturn( FALSE );

		$testee = new UserMetaKeySwitch( $key_map );
		$this->assertSame(
			$default,
			$testee->get_metadata(
				$default, $meta_record[ 'user_id' ], $meta_record[ 'key' ], $meta_record[ 'single' ]
			)
		);
	}

	/**
	 * Recursion can happen when the key_map fails
	 *
	 * @see          UserMetaKeySwitch::get_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_get_metadata_not_recurses( array $meta_record ) {

		$key_map = function ( $key ) use ( $meta_record ) {

			return "{$key}_next";
		};
		$default = 'whatever';
		$testee  = new UserMetaKeySwitch( $key_map );

		Functions::expect( 'get_user_meta' )
			->once()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ] . '_next',
				$meta_record[ 'single' ]
			)
			->andReturnUsing(
				function ( ...$args ) use ( $testee, $meta_record, $default ) {

					// Recurse like `get_user_meta` would do via `get_metadata` filter
					$this->assertSame(
						$default,
						$testee->get_metadata( $default, ...$args )
					);

					return $meta_record[ 'value' ];
				}
			);

		Functions::when( 'metadata_exists' )
			->justReturn( TRUE );

		$this->assertSame(
			$meta_record[ 'value' ],
			$testee->get_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'single' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::update_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_update_metadata( array $meta_record ) {

		$result  = 24;
		$key_map = function ( $key ) use ( $meta_record ) {

			return $meta_record[ 'key' ] === $key
				? $meta_record[ 'mapped_key' ]
				: $key;
		};

		Functions::expect( 'update_user_meta' )
			->twice()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'mapped_key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'previous_value' ]
			)
			->andReturn( $result );

		$testee_override = new UserMetaKeySwitch( $key_map, TRUE );
		$testee          = new UserMetaKeySwitch( $key_map, FALSE );

		$this->assertSame(
			$result,
			$testee_override->update_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'previous_value' ]
			)
		);

		$this->assertNull(
			$testee->update_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'previous_value' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::update_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_update_metadata_ignores_empty_map( array $meta_record ) {

		$key_map = function () {

			return NULL;
		};

		Functions::expect( 'update_user_meta' )
			->never();

		$testee = new UserMetaKeySwitch( $key_map );
		$this->assertNull(
			$testee->update_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'previous_value' ]
			)
		);
	}

	/**
	 * Recursion can happen when the key_map fails
	 *
	 * @see          UserMetaKeySwitch::update_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_update_metadata_not_recurses( array $meta_record ) {

		$key_map = function ( $key ) {

			return "{$key}_next";
		};
		$result  = TRUE;
		$default = NULL;
		$testee  = new UserMetaKeySwitch( $key_map );

		Functions::expect( 'update_user_meta' )
			->once()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ] . '_next',
				$meta_record[ 'value' ],
				$meta_record[ 'previous_value' ]
			)
			->andReturnUsing(
				function ( ...$args ) use ( $testee, $meta_record, $result, $default ) {

					// Recurse like `update_user_meta` would do via `update_metadata` filter
					$this->assertSame(
						$default,
						$testee->update_metadata( $default, ...$args )
					);

					return $result;
				}
			);

		$this->assertSame(
			$result,
			$testee->update_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'previous_value' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::delete_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_delete_metadata( array $meta_record ) {

		$key_map = function ( $key ) use ( $meta_record ) {

			return $meta_record[ 'key' ] === $key
				? $meta_record[ 'mapped_key' ]
				: $key;
		};
		$result  = TRUE;

		Functions::expect( 'delete_user_meta' )
			->twice()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'mapped_key' ],
				$meta_record[ 'value' ]
			)
			->andReturn( $result );

		$testee_override = new UserMetaKeySwitch( $key_map, TRUE );
		$testee          = new UserMetaKeySwitch( $key_map, FALSE );

		$this->assertNull(
			$testee_override->delete_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'delete_all' ]
			)
		);

		$this->assertNull(
			$testee->delete_metadata(
				NULL,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'delete_all' ]
			)
		);

	}

	/**
	 * @see          UserMetaKeySwitch::delete_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_delete_metadata_ignores_empty_map( array $meta_record ) {

		$default = NULL;
		$key_map = function () {

			return NULL;
		};

		Functions::expect( 'delete_user_meta' )
			->never();

		$testee = new UserMetaKeySwitch( $key_map );

		$this->assertSame(
			$default,
			$testee->delete_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'delete_all' ]
			)
		);
	}

	/**
	 * @see          UserMetaKeySwitch::delete_metadata()
	 * @dataProvider meta_data_provider
	 *
	 * @param array $meta_record
	 */
	public function test_delete_metadata_not_recurses( array $meta_record ) {

		$key_map = function ( $key ) {

			return "{$key}_next";
		};
		$testee  = new UserMetaKeySwitch( $key_map );
		$default = NULL;
		$result  = TRUE;

		Functions::expect( 'delete_user_meta' )
			->once()
			->with(
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ] . '_next',
				$meta_record[ 'value' ]
			)
			->andReturnUsing(
				function ( ...$args ) use ( $testee, $default, $result, $meta_record ) {

					$args[] = $meta_record[ 'delete_all' ];
					// Recurse like `delete_user_meta` would do via `delete_metadata` filter
					$this->assertSame(
						$default,
						$testee->delete_metadata( $default, ...$args )
					);

					return $result;
				}
			);

		$this->assertSame(
			$default,
			$testee->delete_metadata(
				$default,
				$meta_record[ 'user_id' ],
				$meta_record[ 'key' ],
				$meta_record[ 'value' ],
				$meta_record[ 'delete_all' ]
			)
		);
	}

	/**
	 * @return array
	 */
	public function meta_data_provider() {

		$data = [];

		$data[ 'single_unique_delete_all_record_with_previous_value' ] = [
			[
				'user_id'        => 42,
				'key'            => '_foo',
				'value'          => 'bar',
				'unique'         => TRUE,
				'mapped_key'     => '_bar',
				'previous_value' => 'foo',
				'single'         => TRUE,
				'delete_all'     => TRUE
			]
		];

		return $data;
	}
}
