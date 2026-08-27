<?php
/**
 * A minimal stand-in for WC_Cart used by unit tests: just the methods the
 * cart-restriction engine calls.
 *
 * @package CartRules_OPTIC
 */

declare( strict_types=1 );

namespace CartRules_OPTIC\Tests\Unit;

class FakeCart {

	/** @var array<string, array{product_id: int}> */
	private $items;

	public function __construct( array $items ) {
		$this->items = $items;
	}

	public function is_empty(): bool {
		return empty( $this->items );
	}

	public function get_cart(): array {
		return $this->items;
	}

	public function remove_cart_item( string $key ): void {
		unset( $this->items[ $key ] );
	}
}
