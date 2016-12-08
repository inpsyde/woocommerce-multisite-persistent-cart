<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WooCommerce\MultisitePersistentCart\Event;

/**
 * Class MetaDataListenerProvider
 *
 * @package Inpsyde\WooCommerce\MultisitePersistentCart\Event
 */
class MetaDataListenerProvider {

	/**
	 * @var MetaDataListener
	 */
	private $listener;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param MetaDataListener $listener
	 * @param string           $type
	 */
	public function __construct( MetaDataListener $listener, $type ) {

		$this->listener = $listener;
		$valid_types    = [ 'user', 'post', 'term', 'comment' ];
		if ( ! in_array( $type, $valid_types ) ) {
			throw new \InvalidArgumentException( "Invalid meta type '{$type}'" );
		}
		$this->type = $type;
	}

	/**
	 * Assigns the listener to the events
	 */
	public function provide_listener() {

		$filters = [
			[
				/* @see add_metadata() */
				'hook'     => "add_{$this->type}_metadata",
				'prio'     => 10,
				'args'     => 5,
				'listener' => [ $this->listener, 'add_metadata' ]
			],
			[
				/* @see get_metadata() */
				'hook'     => "get_{$this->type}_metadata",
				'prio'     => 10,
				'args'     => 4,
				'listener' => [ $this->listener, 'get_metadata' ]
			],
			[
				/* @see update_metadata() */
				'hook'     => "update_{$this->type}_metadata",
				'prio'     => 10,
				'args'     => 5,
				'listener' => [ $this->listener, 'update_metadata' ]
			],
			[
				/* @see delete_metadata() */
				'hook'     => "delete_{$this->type}_metadata",
				'prio'     => 10,
				'args'     => 5,
				'listener' => [ $this->listener, 'delete_metadata' ]
			]
		];

		$added = 0;
		foreach ( $filters as $f ) {
			has_filter( $f[ 'hook' ], $f[ 'listener' ] )
				or add_filter( $f[ 'hook' ], $f[ 'listener' ], $f[ 'prio' ], $f[ 'args' ] )
				and $added++;
		}

		return (bool) $added;
	}
}