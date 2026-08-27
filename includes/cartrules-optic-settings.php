<?php

defined( 'ABSPATH' ) || exit;

/**
 * Adds this module's "One Product Type in Cart" section to the shared "CartRules" tab.
 */

add_filter( 'woocommerce_get_sections_cartrules', 'cartrules_optic_add_settings_section' );
add_filter( 'woocommerce_get_settings_cartrules', 'cartrules_optic_settings_fields', 10, 2 );

function cartrules_optic_add_settings_section( array $sections ): array {
	$sections['optic'] = __( 'One Product Type in Cart', 'cartrules-one-product-type-in-cart-for-woocommerce' );

	return $sections;
}

function cartrules_optic_settings_fields( array $settings, string $section_id ): array {
	if ( 'optic' !== $section_id ) {
		return $settings;
	}

	return array(
		array(
			'title' => __( 'One Product Type in Cart', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'type'  => 'title',
			'desc'  => __( 'Prevent customers from mixing different product types (e.g. simple, variable, grouped, external) in the same cart.', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'id'    => 'cartrules_optic_settings_title',
		),
		array(
			'title'   => __( 'Enable restriction', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'desc'    => __( 'Only allow products of one product type in the cart at a time', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'id'      => 'cartrules_optic_enabled',
			'default' => 'no',
			'type'    => 'checkbox',
		),
		array(
			'title'   => __( 'When a different product type is added', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'desc'    => __( 'Choose what happens when a customer tries to add a product of a different type', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'id'      => 'cartrules_optic_mode',
			'default' => 'deny',
			'type'    => 'select',
			'class'   => 'wc-enhanced-select',
			'options' => array(
				'deny'    => __( 'Block the new product and show an error', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
				'replace' => __( 'Empty the cart first, then add the new product', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			),
		),
		array(
			'title'    => __( 'Blocked message', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'desc_tip' => __( 'Shown when a product is blocked. Use {product_type} for the product type already in the cart.', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'id'       => 'cartrules_optic_deny_message',
			'default'  => __( 'You already have "{product_type}" products in your cart. Please remove them first, or complete that order separately.', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'type'     => 'textarea',
			'css'      => 'width:100%; height: 75px;',
		),
		array(
			'title'    => __( 'Replaced message', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'desc_tip' => __( 'Shown when the cart is emptied and replaced. Use {product_type} for the product type that was removed.', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'id'       => 'cartrules_optic_replace_message',
			'default'  => __( 'Your cart contained "{product_type}" products, so we replaced them with your new selection.', 'cartrules-one-product-type-in-cart-for-woocommerce' ),
			'type'     => 'textarea',
			'css'      => 'width:100%; height: 75px;',
		),
		array(
			'type' => 'sectionend',
			'id'   => 'cartrules_optic_settings_end',
		),
	);
}
