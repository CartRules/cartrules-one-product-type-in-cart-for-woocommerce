<?php

defined( 'ABSPATH' ) || exit;

/**
 * Blocks or replaces cart contents when a product of a different product type is added.
 */
class CartRules_OPTIC_Cart_Restriction {

	/**
	 * Cart item keys staged for removal in "replace" mode, keyed by the product id that
	 * triggered the replacement. Populated during validation, consumed by handle_replace().
	 *
	 * @var array<int, array{cart_item_keys: string[], type_label: string}>
	 */
	private $pending_replacements = array();

	public function __construct() {
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 10, 2 );
		add_action( 'woocommerce_add_to_cart', array( $this, 'handle_replace' ), 10, 2 );
	}

	/**
	 * Other plugins may also hook woocommerce_add_to_cart_validation. Removing cart items here
	 * instead of in handle_replace() would mutate the cart mid-filter-chain, so a plugin
	 * validating later would see an already-emptied cart and skip its own check.
	 *
	 * @param bool $passed
	 * @param int  $product_id
	 * @return bool
	 */
	public function validate_add_to_cart( bool $passed, int $product_id ): bool {
		if ( ! $passed || 'yes' !== get_option( 'cartrules_optic_enabled', 'no' ) || WC()->cart->is_empty() ) {
			return $passed;
		}

		$new_type = $this->get_product_type( $product_id );

		if ( '' === $new_type ) {
			return $passed;
		}

		$cart_types = array();

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_type = $this->get_product_type( $cart_item['product_id'] );

			if ( '' !== $cart_type ) {
				$cart_types[] = $cart_type;
			}
		}

		$cart_types = array_values( array_unique( $cart_types ) );

		if ( empty( $cart_types ) || in_array( $new_type, $cart_types, true ) ) {
			return $passed;
		}

		$existing_type_label = $this->get_product_type_label( $cart_types[0] );

		if ( 'replace' === get_option( 'cartrules_optic_mode', 'deny' ) ) {
			$this->pending_replacements[ $product_id ] = array(
				'cart_item_keys' => array_keys( WC()->cart->get_cart() ),
				'type_label'     => $existing_type_label,
			);

			return true;
		}

		wc_add_notice( $this->build_deny_message( $existing_type_label ), 'error' );

		return false;
	}

	/**
	 * Runs once WooCommerce has actually added the new item to the cart, so every plugin
	 * hooked into the validation filter has already had a chance to evaluate the original cart.
	 *
	 * @param string $cart_item_key
	 * @param int    $product_id
	 */
	public function handle_replace( string $cart_item_key, int $product_id ): void {
		if ( ! isset( $this->pending_replacements[ $product_id ] ) ) {
			return;
		}

		$replacement = $this->pending_replacements[ $product_id ];
		unset( $this->pending_replacements[ $product_id ] );

		foreach ( $replacement['cart_item_keys'] as $conflicting_item_key ) {
			WC()->cart->remove_cart_item( $conflicting_item_key );
		}

		wc_add_notice( $this->build_replace_message( $replacement['type_label'] ), 'notice' );
	}

	/**
	 * The message options are only ever written to the database when the settings screen is
	 * saved, so get_option() needs the same fallback the settings screen shows in the
	 * textarea by default -- otherwise enabling this via wp_cli/wp option update, or a site
	 * migration that drops the option row, silently produces a blank notice.
	 */
	private function build_deny_message( string $type_label ): string {
		$default = __( 'You already have "{product_type}" products in your cart. Please remove them first, or complete that order separately.', 'cartrules-one-product-type-in-cart-for-woocommerce' );

		return str_replace( '{product_type}', $type_label, get_option( 'cartrules_optic_deny_message', $default ) );
	}

	private function build_replace_message( string $type_label ): string {
		$default = __( 'Your cart contained "{product_type}" products, so we replaced them with your new selection.', 'cartrules-one-product-type-in-cart-for-woocommerce' );

		return str_replace( '{product_type}', $type_label, get_option( 'cartrules_optic_replace_message', $default ) );
	}

	/**
	 * A product has exactly one type, like shipping class and unlike categories/tags/brands.
	 * $product_id is always the parent id, even for a variation being added to the cart, so
	 * every variation of a variable product is consistently treated as type "variable".
	 */
	private function get_product_type( int $product_id ): string {
		$product = wc_get_product( $product_id );

		return $product ? $product->get_type() : '';
	}

	/**
	 * wc_get_product_types() only lists the types WooCommerce (or an extension) has
	 * registered a label for. Falling back to the raw slug covers a type added by a
	 * plugin that never registered one, rather than showing a blank label.
	 */
	private function get_product_type_label( string $type ): string {
		$types = wc_get_product_types();

		return isset( $types[ $type ] ) ? $types[ $type ] : $type;
	}
}
