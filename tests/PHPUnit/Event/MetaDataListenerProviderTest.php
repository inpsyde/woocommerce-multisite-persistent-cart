<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WooCommerce\MultisitePersistentCart\Event;

use Brain\Monkey\Functions;
use Brain\Monkey\WP\Filters;
use MonkeryTestCase\BrainMonkeyWpTestCase;
use Mockery;

/**
 * Class MetaDataListenerProviderTest
 *
 * @package Inpsyde\WooCommerce\MultisitePersistentCart\Event
 */
class MetaDataListenerProviderTest extends BrainMonkeyWpTestCase  {

	/**
	 * @see MetaDataListenerProvider::provide_listener()
	 */
	public function test_provide_listener() {

		$listener = Mockery::mock( MetaDataListener::class );

		Functions::expect( 'has_filter' )
			->once()
			->with( 'add_user_metadata', [ $listener, 'add_metadata' ] )
			->andReturn( FALSE );
		Functions::expect( 'has_filter' )
			->once()
			->with( 'get_user_metadata', [ $listener, 'get_metadata' ] )
			->andReturn( FALSE );
		Functions::expect( 'has_filter' )
			->once()
			->with( 'update_user_metadata', [ $listener, 'update_metadata' ] )
			->andReturn( FALSE );
		Functions::expect( 'has_filter' )
			->once()
			->with( 'delete_user_metadata', [ $listener, 'delete_metadata' ] )
			->andReturn( FALSE );

		$testee   = new MetaDataListenerProvider( $listener, 'user' );

		Filters::expectAdded( 'add_user_metadata' )
			->once()
			->with( [ $listener, 'add_metadata' ], 10, 5 );

		Filters::expectAdded( 'get_user_metadata' )
			->once()
			->with( [ $listener, 'get_metadata' ], 10, 4 );

		Filters::expectAdded( 'update_user_metadata' )
			->once()
			->with( [ $listener, 'update_metadata' ], 10, 5 );

		Filters::expectAdded( 'delete_user_metadata' )
			->once()
			->with( [ $listener, 'delete_metadata' ], 10, 5 );

		$this->assertTrue(
			$testee->provide_listener()
		);
	}

	/**
	 * @see MetaDataListenerProvider::provide_listener()
	 */
	public function test_provide_listener_only_once() {

		$listener = Mockery::mock( MetaDataListener::class );

		Functions::when( 'has_filter' )
			->justReturn( TRUE );

		$testee   = new MetaDataListenerProvider( $listener, 'user' );

		Filters::expectAdded( 'add_user_metadata' )
			->never();

		Filters::expectAdded( 'get_user_metadata' )
			->never();

		Filters::expectAdded( 'update_user_metadata' )
			->never();

		Filters::expectAdded( 'delete_user_metadata' )
			->never();

		$this->assertFalse(
			$testee->provide_listener()
		);
	}

	/**
	 * @see MetaDataListenerProvider::__construct()
	 */
	public function test_constructor_throws_exception() {

		$this->expectException( 'InvalidArgumentException' );
		$listener = Mockery::mock( MetaDataListener::class );

		new MetaDataListenerProvider( $listener, 'bad-value' );
	}

	/**
	 * @see MetaDataListenerProvider::__construct()
	 */
	public function test_valid_meta_types() {

		$valid_types = [ 'user', 'post', 'term', 'comment' ];
		$listener = Mockery::mock( MetaDataListener::class );

		foreach ( $valid_types as $t ) {
			$this->assertInstanceOf(
				MetaDataListenerProvider::class,
				new MetaDataListenerProvider( $listener, $t )
			);
		}
	}
}
