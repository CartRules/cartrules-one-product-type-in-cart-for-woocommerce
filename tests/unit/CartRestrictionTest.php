<?php
/**
 * Unit tests for the cart-restriction engine — the actual "one product type at
 * a time" rule. No WordPress is loaded; WC()/get_option()/product functions
 * are mocked with WP_Mock.
 *
 * @package CartRules_OPTIC
 */

declare( strict_types=1 );

namespace CartRules_OPTIC\Tests\Unit;

use WP_Mock;
use WP_Mock\Tools\TestCase as WPMockTestCase;

require_once dirname( __DIR__, 2 ) . '/includes/class-cartrules-optic-cart-restriction.php';
require_once __DIR__ . '/FakeCart.php';

class CartRestrictionTest extends WPMockTestCase {

	private function mock_cart( array $items ): void {
		$wc       = new \stdClass();
		$wc->cart = new FakeCart( $items );

		WP_Mock::userFunction( 'WC' )->andReturn( $wc );
	}

	/**
	 * @param array<int, string> $product_to_type Product id => product type slug.
	 */
	private function mock_product_types( array $product_to_type ): void {
		WP_Mock::userFunction( 'wc_get_product' )->andReturnUsing(
			function ( $product_id ) use ( $product_to_type ) {
				if ( ! isset( $product_to_type[ $product_id ] ) ) {
					return false;
				}

				$product = \Mockery::mock();
				$product->shouldReceive( 'get_type' )->andReturn( $product_to_type[ $product_id ] );

				return $product;
			}
		);

		WP_Mock::userFunction( 'wc_get_product_types' )->andReturn(
			array(
				'simple'   => 'Simple product',
				'variable' => 'Variable product',
				'grouped'  => 'Grouped product',
				'external' => 'External/Affiliate product',
			)
		);
	}

	public function test_denies_a_different_type_in_deny_mode() {
		$restriction = new \CartRules_OPTIC_Cart_Restriction();

		$this->mock_cart( array( 'key_1' => array( 'product_id' => 1 ) ) );
		$this->mock_product_types(
			array(
				1 => 'simple',
				2 => 'variable',
			)
		);

		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_enabled', 'no' )->andReturn( 'yes' );
		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_mode', 'deny' )->andReturn( 'deny' );
		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_deny_message', WP_Mock\Functions::type( 'string' ) )->andReturn( 'Blocked: {product_type}' );
		WP_Mock::userFunction( 'wc_add_notice' )->with( 'Blocked: Simple product', 'error' )->once();

		$this->assertFalse( $restriction->validate_add_to_cart( true, 2 ) );
	}

	/**
	 * The message options only exist in the database once the settings screen has actually
	 * been saved. Enabling the restriction any other way (wp_cli, a partial options-table
	 * restore) must still produce a real message, not a blank notice.
	 */
	public function test_falls_back_to_the_default_message_when_the_option_was_never_saved() {
		$restriction = new \CartRules_OPTIC_Cart_Restriction();

		$this->mock_cart( array( 'key_1' => array( 'product_id' => 1 ) ) );
		$this->mock_product_types(
			array(
				1 => 'simple',
				2 => 'variable',
			)
		);

		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_enabled', 'no' )->andReturn( 'yes' );
		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_mode', 'deny' )->andReturn( 'deny' );
		// Simulate an unset option: get_option() returns whatever default it was called with.
		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_deny_message', WP_Mock\Functions::type( 'string' ) )
			->andReturnUsing( static fn( $option, $fallback ) => $fallback );
		WP_Mock::userFunction( '__' )->andReturnUsing( static fn( $text ) => $text );
		WP_Mock::userFunction( 'wc_add_notice' )
			->with( WP_Mock\Functions::type( 'string' ), 'error' )
			->once()
			->andReturnUsing(
				function ( $message ) {
					$this->assertStringContainsString( 'Simple product', $message );
					$this->assertNotSame( '', $message );
				}
			);

		$this->assertFalse( $restriction->validate_add_to_cart( true, 2 ) );
	}

	public function test_replace_mode_empties_the_cart_instead_of_blocking() {
		$restriction = new \CartRules_OPTIC_Cart_Restriction();

		$this->mock_cart( array( 'key_1' => array( 'product_id' => 1 ) ) );
		$this->mock_product_types(
			array(
				1 => 'simple',
				2 => 'variable',
			)
		);

		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_enabled', 'no' )->andReturn( 'yes' );
		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_mode', 'deny' )->andReturn( 'replace' );
		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_replace_message', WP_Mock\Functions::type( 'string' ) )->andReturn( 'Replaced: {product_type}' );
		WP_Mock::userFunction( 'wc_add_notice' )->with( 'Replaced: Simple product', 'notice' )->once();

		$this->assertTrue( $restriction->validate_add_to_cart( true, 2 ) );

		$restriction->handle_replace( 'new_key', 2 );
	}

	public function test_allows_a_shared_type() {
		$restriction = new \CartRules_OPTIC_Cart_Restriction();

		$this->mock_cart( array( 'key_1' => array( 'product_id' => 1 ) ) );
		$this->mock_product_types(
			array(
				1 => 'simple',
				2 => 'simple',
			)
		);

		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_enabled', 'no' )->andReturn( 'yes' );

		$this->assertTrue( $restriction->validate_add_to_cart( true, 2 ) );
	}

	public function test_disabled_setting_lets_everything_through() {
		$restriction = new \CartRules_OPTIC_Cart_Restriction();

		WP_Mock::userFunction( 'get_option' )->with( 'cartrules_optic_enabled', 'no' )->andReturn( 'no' );

		$this->assertTrue( $restriction->validate_add_to_cart( true, 2 ) );
	}
}
